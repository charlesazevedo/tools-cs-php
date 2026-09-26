"""Roda o experimento (L6): a linha de base e cada variante, nas cinco
ferramentas e nas duas configurações. Pressupõe `preparar.py` já rodado.

    python3 experimento/executar.py                       # tudo o que ainda não rodou
    python3 experimento/executar.py --so base C13-v1      # só essas execuções
    python3 experimento/executar.py --fase locais         # sem o SonarQube
    python3 experimento/executar.py --refazer             # ignora resultados anteriores

Para cada execução (`base` ou `<Cxx>-<vN>`), em experimento/trabalho/execucoes/<id>/:
- projeto/  cópia do PDVLaravel original (vendor/ é um link para o da base),
            com a variante aplicada (ver exemplos/README.md);
- injecao.json  onde a variante entrou (faixa de linhas na CaixaController,
            arquivos novos), usado pelo analisar.py;
- saidas/   saída bruta de cada ferramenta e configuração;
- avisos.csv  os avisos normalizados (ferramenta, configuração, regra, arquivo, linha);
- estado.json  código de saída, duração e erros de cada ferramenta.
"""

import argparse
import csv
import json
import os
import re
import shutil
import time
from concurrent.futures import ThreadPoolExecutor

from comum import (BASE, CONFIGURACOES, CONTROLLER, EXECUCOES, EXPERIMENTO, FONTES,
                   PERFIL_COMPLETO, PROJETO_COMPOSE, REFERENCIAS, caminho_no_conteiner,
                   id_execucao, ler_variantes, no_conteiner, pasta_da_variante, sh, sonar,
                   token_sonar)

PHAR = "/opt/ferramentas"
LOCAIS = ["PHPMD", "PHPCS", "PHPStan", "Psalm"]


# --- Montagem do projeto e aplicação da variante --------------------------------

def montar_projeto(execucao, variante):
    pasta = EXECUCOES / execucao
    projeto = pasta / "projeto"
    if pasta.exists():
        shutil.rmtree(pasta)
    shutil.copytree(BASE, projeto, symlinks=True, ignore=shutil.ignore_patterns("vendor", ".commit"))
    os.symlink(os.path.relpath(BASE / "vendor", projeto), projeto / "vendor")
    injecao = {"execucao": execucao, "controller": None, "arquivos_novos": [], "referencias": False}
    if variante is not None:
        injecao.update(aplicar_variante(projeto, variante))
    escrever_configs(projeto)
    (pasta / "saidas").mkdir()
    (pasta / "injecao.json").write_text(json.dumps(injecao, indent=2, ensure_ascii=False) + "\n",
                                        encoding="utf-8")
    return pasta


def aplicar_variante(projeto, v):
    base_var = pasta_da_variante(v["cs_id"], v["variante"])
    resultado = {"cs_id": v["cs_id"], "variante": v["variante"], "tipo": v["tipo"]}
    if "membros" in v["tipo"]:
        resultado["controller"] = inserir_membros(projeto / CONTROLLER, base_var / "membros.php")
    novos = []
    if "arquivos" in v["tipo"]:
        origem = base_var / "arquivos"
        for arq in sorted(p for p in origem.rglob("*") if p.is_file()):
            rel = arq.relative_to(origem).as_posix()
            destino = projeto / rel
            if destino.exists():
                raise SystemExit(f"{v['cs_id']}/{v['variante']}: {rel} já existe no projeto")
            destino.parent.mkdir(parents=True, exist_ok=True)
            shutil.copy2(arq, destino)
            novos.append(rel)
    resultado["arquivos_novos"] = novos
    classes = classes_novas(projeto, novos)
    if classes:
        escrever_referencias(projeto, classes)
        resultado["referencias"] = True
    return resultado


def inserir_membros(controller, membros_php):
    """Insere o corpo de `class Fragmento { ... }` antes da chave que fecha a
    CaixaController. Devolve a faixa de linhas inseridas (1-based, inclusive)."""
    texto = membros_php.read_text(encoding="utf-8")
    m = re.search(r"^class\s+Fragmento\s*\{\s*?\n", texto, re.M)
    fim = texto.rstrip().rfind("}")
    corpo = texto[m.end():fim].rstrip("\n").split("\n")
    linhas = controller.read_text(encoding="utf-8").split("\n")
    fecha = max(i for i, l in enumerate(linhas) if l.rstrip() == "}")   # chave da classe (0-based)
    novas = [""] + corpo                                                   # linha em branco antes (PSR-12)
    linhas[fecha:fecha] = novas
    controller.write_text("\n".join(linhas), encoding="utf-8")
    inicio = fecha + 1                                                      # 1-based
    return {"arquivo": CONTROLLER, "inicio": inicio, "fim": inicio + len(novas) - 1,
            "deslocamento": len(novas), "linha_fecha_base": fecha + 1}


def classes_novas(projeto, novos):
    classes = []
    for rel in novos:
        if not (rel.startswith("app/") and rel.endswith(".php")):
            continue
        t = (projeto / rel).read_text(encoding="utf-8")
        ns = re.search(r"^namespace\s+([\w\\]+);", t, re.M)
        for c in re.findall(r"^\s*(?:abstract\s+|final\s+)?class\s+(\w+)", t, re.M):
            classes.append((ns.group(1) + "\\" if ns else "") + c)
    return classes


def escrever_referencias(projeto, classes):
    """O Psalm não analisa os membros de classes que nunca são referenciadas
    (acusa UnusedClass e para). Este arquivo de apoio referencia as classes
    novas da variante; os avisos nele não contam (analisar.py o ignora)."""
    itens = "\n".join(f"            \\{c}::class," for c in classes)
    (projeto / REFERENCIAS).parent.mkdir(parents=True, exist_ok=True)
    (projeto / REFERENCIAS).write_text(f"""<?php

namespace App\\Experimento;

/**
 * Arquivo de apoio do experimento (não faz parte da variante).
 */
class Referencias
{{
    /**
     * Classes criadas pela variante.
     *
     * @return string[]
     */
    public function classes()
    {{
        return [
{itens}
        ];
    }}
}}
""", encoding="utf-8")


def escrever_configs(projeto):
    """PHPStan e Psalm resolvem caminhos em relação ao arquivo de configuração,
    então cada execução recebe cópias na raiz do projeto, analisando FONTES."""
    cfg = EXPERIMENTO / "config"
    for c in CONFIGURACOES:
        neon = (cfg / "phpstan" / f"phpstan-{c}.neon").read_text(encoding="utf-8")
        caminhos = "".join(f"\t\t- {f}\n" for f in FONTES)
        neon, n = re.subn(r"\tpaths:\n(?:\t\t- .*\n)+", "\tpaths:\n" + caminhos, neon)
        assert n == 1, "bloco paths não encontrado no neon"
        (projeto / f"phpstan-{c}.neon").write_text(neon, encoding="utf-8")
        xml = (cfg / "psalm" / f"psalm-{c}.xml").read_text(encoding="utf-8")
        dirs = "\n        ".join(f'<directory name="{f}" />' for f in FONTES)
        xml, n = re.subn(r'<directory name="app" />', dirs, xml, count=1)
        assert n == 1, "projectFiles não encontrado no xml do Psalm"
        (projeto / f"psalm-{c}.xml").write_text(xml, encoding="utf-8")


# --- Ferramentas locais --------------------------------------------------------

def comando(ferramenta, c, saida):
    if ferramenta == "PHPMD":
        return (f"php {PHAR}/phpmd.phar {','.join(FONTES)} json /config/phpmd/phpmd-{c}.xml "
                f"--reportfile {saida}")
    if ferramenta == "PHPCS":
        return (f"php {PHAR}/phpcs.phar -q --standard=/config/phpcs/phpcs-{c}.xml --extensions=php "
                f"--parallel=4 --report=json --report-file={saida} {' '.join(FONTES)}")
    if ferramenta == "PHPStan":
        return (f"php {PHAR}/phpstan.phar analyse -c phpstan-{c}.neon --error-format=json "
                f"--no-progress --memory-limit=4G > {saida}")
    if ferramenta == "Psalm":
        return (f"php {PHAR}/psalm.phar -c psalm-{c}.xml --no-cache --no-progress --threads=4 "
                f"--output-format=json > {saida}")
    raise ValueError(ferramenta)


def rodar_locais(pasta):
    projeto_c = caminho_no_conteiner(pasta / "projeto")
    estado = {}
    for c in CONFIGURACOES:
        for f in LOCAIS:
            saida = pasta / "saidas" / f"{f.lower()}-{c}.json"
            t0 = time.time()
            r = no_conteiner(comando(f, c, caminho_no_conteiner(saida)), cwd=projeto_c)
            estado[f"{f}/{c}"] = {"codigo": r.returncode, "segundos": round(time.time() - t0, 1),
                                  "stderr": r.stderr[-1500:],
                                  "saida_ok": saida.exists() and saida.stat().st_size > 0}
    return estado


# --- SonarQube -----------------------------------------------------------------

def rodar_sonar(pasta, execucao):
    estado = {}
    for c in CONFIGURACOES:
        chave = f"cs-exp-{execucao}-{c}"
        t0 = time.time()
        if sonar("GET", "api/projects/search", projects=chave).get("components"):
            sonar("POST", "api/projects/delete", project=chave)
        sonar("POST", "api/projects/create", project=chave, name=chave)
        if c == "completa":
            for lang in ("php", "web"):
                sonar("POST", "api/qualityprofiles/add_project", language=lang,
                      qualityProfile=PERFIL_COMPLETO, project=chave)
        r = sh(["docker", "compose", "-p", PROJETO_COMPOSE, "run", "--rm", "-e", "SONAR_TOKEN", "scanner",
                "sonar-scanner", f"-Dsonar.projectKey={chave}",
                f"-Dsonar.projectBaseDir={caminho_no_conteiner(pasta / 'projeto')}",
                f"-Dsonar.sources={','.join(FONTES)}", "-Dsonar.scm.disabled=true",
                "-Dsonar.qualitygate.wait=true", "-Dsonar.qualitygate.timeout=900"],
               cwd=EXPERIMENTO, env=dict(os.environ, SONAR_TOKEN=token_sonar()), timeout=1800)
        issues = buscar_issues(chave)
        (pasta / "saidas" / f"sonarqube-{c}.json").write_text(json.dumps(issues, ensure_ascii=False),
                                                              encoding="utf-8")
        estado[f"SonarQube/{c}"] = {"codigo": r.returncode, "segundos": round(time.time() - t0, 1),
                                    "stderr": (r.stdout + r.stderr)[-1500:], "saida_ok": r.returncode == 0,
                                    "issues": len(issues)}
    return estado


def buscar_issues(chave):
    issues, pagina = [], 1
    while True:
        r = sonar("GET", "api/issues/search", components=chave, ps=500, p=pagina)
        issues += r.get("issues", [])
        total = r.get("paging", r).get("total", r.get("total", 0))
        if total > 10000:
            raise SystemExit(f"{chave}: {total} issues, acima do limite de paginação da API")
        if pagina * 500 >= total:
            return issues
        pagina += 1


# --- Normalização ----------------------------------------------------------------

def normalizar(pasta):
    raiz = caminho_no_conteiner(pasta / "projeto") + "/"
    linhas = []

    def rel(caminho):
        caminho = caminho.replace(raiz, "")
        return caminho.split(":", 1)[1] if caminho.startswith("cs-exp-") else caminho

    for c in CONFIGURACOES:
        s = pasta / "saidas"
        f = s / f"phpmd-{c}.json"
        if f.exists() and f.stat().st_size:
            for arq in json.loads(f.read_text()).get("files", []):
                for v in arq.get("violations", []):
                    linhas.append(("PHPMD", c, v["rule"], rel(arq["file"]), v["beginLine"], v.get("description", "")))
        f = s / f"phpcs-{c}.json"
        if f.exists() and f.stat().st_size:
            for arq, d in json.loads(f.read_text()).get("files", {}).items():
                for m in d.get("messages", []):
                    linhas.append(("PHPCS", c, m["source"], rel(arq), m["line"], m.get("message", "")))
        f = s / f"phpstan-{c}.json"
        if f.exists() and f.stat().st_size:
            d = json.loads(f.read_text())
            for arq, dd in (d.get("files") or {}).items():
                for m in dd.get("messages", []):
                    linhas.append(("PHPStan", c, m.get("identifier", ""), rel(arq), m.get("line") or 0,
                                   m.get("message", "")))
        f = s / f"psalm-{c}.json"
        if f.exists() and f.stat().st_size:
            for m in json.loads(f.read_text()):
                linhas.append(("Psalm", c, m["type"], rel(m["file_path"] if m.get("file_path") else m["file_name"]),
                               m["line_from"], m.get("message", "")))
        f = s / f"sonarqube-{c}.json"
        if f.exists() and f.stat().st_size:
            for m in json.loads(f.read_text()):
                linhas.append(("SonarQube", c, m["rule"], rel(m["component"]), m.get("line", 0),
                               m.get("message", "")))
    with open(pasta / "avisos.csv", "w", newline="", encoding="utf-8") as fh:
        w = csv.writer(fh, lineterminator="\n")
        w.writerow(["ferramenta", "configuracao", "regra", "arquivo", "linha", "mensagem"])
        for l in linhas:
            w.writerow([*l[:5], l[5].replace("\n", " ")[:200]])
    return len(linhas)


# --- Orquestração ----------------------------------------------------------------

def execucoes_planejadas(filtro):
    lista = [("base", None)] + [(id_execucao(v["cs_id"], v["variante"]), v) for v in ler_variantes()]
    return [x for x in lista if not filtro or x[0] in filtro]


def estado_de(pasta):
    f = pasta / "estado.json"
    return json.loads(f.read_text()) if f.exists() else {}


def salvar_estado(pasta, novo):
    estado = estado_de(pasta)
    estado.update(novo)
    (pasta / "estado.json").write_text(json.dumps(estado, indent=2, ensure_ascii=False) + "\n", encoding="utf-8")


def fase_locais(execucao, variante, refazer):
    pasta = EXECUCOES / execucao
    feito = estado_de(pasta)
    if not refazer and all(feito.get(f"{f}/{c}", {}).get("saida_ok") for f in LOCAIS for c in CONFIGURACOES):
        return f"{execucao}: locais já rodadas"
    montar_projeto(execucao, variante)
    t0 = time.time()
    salvar_estado(pasta, rodar_locais(pasta))
    n = normalizar(pasta)
    return f"{execucao}: locais em {time.time() - t0:.0f} s, {n} avisos"


def fase_sonar(execucao, refazer):
    pasta = EXECUCOES / execucao
    feito = estado_de(pasta)
    if not (pasta / "projeto").exists():
        return f"{execucao}: projeto não montado (rode a fase locais antes)"
    if not refazer and all(f"SonarQube/{c}" in feito and feito[f"SonarQube/{c}"]["saida_ok"]
                           for c in CONFIGURACOES):
        return f"{execucao}: SonarQube já rodado"
    t0 = time.time()
    salvar_estado(pasta, rodar_sonar(pasta, execucao))
    n = normalizar(pasta)
    return f"{execucao}: SonarQube em {time.time() - t0:.0f} s, {n} avisos no total"


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--so", nargs="*", default=[])
    ap.add_argument("--fase", choices=["locais", "sonar", "todas"], default="todas")
    ap.add_argument("--refazer", action="store_true")
    ap.add_argument("--paralelo", type=int, default=3)
    a = ap.parse_args()
    planejadas = execucoes_planejadas(set(a.so))
    EXECUCOES.mkdir(parents=True, exist_ok=True)
    if a.fase in ("locais", "todas"):
        with ThreadPoolExecutor(a.paralelo) as ex:
            for msg in ex.map(lambda x: fase_locais(x[0], x[1], a.refazer), planejadas):
                print(msg, flush=True)
    if a.fase in ("sonar", "todas"):
        for execucao, _ in planejadas:        # o SonarQube processa uma análise por vez
            print(fase_sonar(execucao, a.refazer), flush=True)


if __name__ == "__main__":
    main()
