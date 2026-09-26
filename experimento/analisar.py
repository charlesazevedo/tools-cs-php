"""Aplica o critério de detecção (decisão D4) às execuções do experimento.

    python3 experimento/analisar.py

Um CS é detectado numa variante, por uma ferramenta numa configuração, quando
há ao menos um aviso que:
1. vem de uma regra mapeada para o CS em dados/regras.csv;
2. aponta para o que a variante injetou: um arquivo novo da variante, ou as
   linhas inseridas na CaixaController, ou a declaração da CaixaController
   (avisos de nível de classe; esclarecimento da D4);
3. não existe na linha de base (mesma ferramenta, configuração, regra,
   arquivo e linha, com as linhas da CaixaController corrigidas pelo
   deslocamento da inserção).

O arquivo de apoio app/Experimento/Referencias.php nunca conta.

Saídas em experimento/resultados/:
- deteccoes-variantes.csv  uma linha por variante × ferramenta × configuração;
- deteccoes-cs.csv         por CS: quantas variantes cada ferramenta detectou;
- avisos-novos.csv         todos os avisos novos nos locais da variante, mapeados ou não;
- linha-de-base.csv        contagem de avisos da linha de base por ferramenta e configuração.
"""

import csv
import json
import re
from collections import Counter, defaultdict

from comum import (BASE, CONFIGURACOES, CONTROLLER, EXECUCOES, FERRAMENTAS, RAIZ, REFERENCIAS,
                   RESULTADOS, id_execucao, ler_variantes)


def regras_por_cs():
    mapa = defaultdict(set)
    with open(RAIZ / "dados" / "regras.csv", encoding="utf-8") as f:
        for r in csv.DictReader(f):
            if r["regra"].strip() == "—":
                continue
            for ident in r["regra"].split(" — ")[0].split(" / "):
                mapa[(r["cs_id"], r["ferramenta"])].add(ident.strip())
    return mapa


def casa(ferramenta, regra_aviso, idents):
    if ferramenta == "PHPCS":   # sniff (Std.Cat.Sniff) ou código completo (Std.Cat.Sniff.Codigo)
        return any(regra_aviso == i or regra_aviso.startswith(i + ".") for i in idents)
    return regra_aviso in idents


def ler_avisos(execucao):
    with open(EXECUCOES / execucao / "avisos.csv", encoding="utf-8") as f:
        return [dict(r, linha=int(r["linha"] or 0)) for r in csv.DictReader(f)]


def chave(a, linha=None):
    return (a["ferramenta"], a["configuracao"], a["regra"], a["arquivo"], a["linha"] if linha is None else linha)


def linha_da_classe():
    for i, l in enumerate((BASE / CONTROLLER).read_text(encoding="utf-8").split("\n"), 1):
        if re.match(r"\s*class\s+CaixaController\b", l):
            return i
    raise SystemExit("declaração da CaixaController não encontrada")


def main():
    regras = regras_por_cs()
    base = ler_avisos("base")
    estado_base = json.loads((EXECUCOES / "base" / "estado.json").read_text())
    decl = linha_da_classe()
    variantes = ler_variantes()
    RESULTADOS.mkdir(parents=True, exist_ok=True)

    linhas_var, novos_todos = [], []
    for v in variantes:
        execucao = id_execucao(v["cs_id"], v["variante"])
        pasta = EXECUCOES / execucao
        if not (pasta / "avisos.csv").exists():
            print(f"{execucao}: sem resultados, pulando")
            continue
        inj = json.loads((pasta / "injecao.json").read_text())
        estado = json.loads((pasta / "estado.json").read_text())
        ctl = inj["controller"]

        def mapear_base(a):
            if ctl and a["arquivo"] == CONTROLLER and a["linha"] >= ctl["inicio"]:
                return chave(a, a["linha"] + ctl["deslocamento"])
            return chave(a)

        na_base = {mapear_base(a) for a in base}
        novos_arquivos = set(inj["arquivos_novos"]) - {REFERENCIAS}

        def no_local(a):
            if a["arquivo"] in novos_arquivos:
                return True
            if ctl and a["arquivo"] == CONTROLLER:
                return ctl["inicio"] <= a["linha"] <= ctl["fim"] or a["linha"] == decl
            return False

        novos = [a for a in ler_avisos(execucao) if no_local(a) and chave(a) not in na_base]
        for a in novos:
            novos_todos.append({"cs_id": v["cs_id"], "variante": v["variante"], **a})
        for c in CONFIGURACOES:
            esperado = {x.split(":", 1)[0] for x in v[f"esperado_{c}"].split("; ") if x}
            for f in FERRAMENTAS:
                ok_ferr = estado.get(f"{f}/{c}", {}).get("saida_ok", False) and \
                    estado_base.get(f"{f}/{c}", {}).get("saida_ok", False)
                idents = regras.get((v["cs_id"], f), set())
                achados = [a for a in novos if a["ferramenta"] == f and a["configuracao"] == c
                           and casa(f, a["regra"], idents)]
                detectado = "erro" if not ok_ferr else ("Sim" if achados else "Não")
                linhas_var.append({
                    "cs_id": v["cs_id"], "variante": v["variante"], "configuracao": c, "ferramenta": f,
                    "detectado": detectado, "esperado": "Sim" if f in esperado else "Não",
                    "regras_disparadas": "; ".join(sorted({a["regra"] for a in achados})),
                    "avisos_mapeados": len(achados),
                    "avisos_novos_no_local": sum(1 for a in novos if a["ferramenta"] == f
                                                 and a["configuracao"] == c)})

    campos = ["cs_id", "variante", "configuracao", "ferramenta", "detectado", "esperado",
              "regras_disparadas", "avisos_mapeados", "avisos_novos_no_local"]
    escrever("deteccoes-variantes.csv", campos, linhas_var)

    por_cs = defaultdict(list)
    for l in linhas_var:
        por_cs[(l["cs_id"], l["configuracao"], l["ferramenta"])].append(l["detectado"])
    agregadas = []
    for (cs, c, f), dets in sorted(por_cs.items()):
        n = sum(d == "Sim" for d in dets)
        res = "erro" if "erro" in dets else ("todas" if n == len(dets) else "algumas" if n else "nenhuma")
        agregadas.append({"cs_id": cs, "configuracao": c, "ferramenta": f,
                          "variantes_detectadas": n, "variantes": len(dets), "resultado": res})
    escrever("deteccoes-cs.csv", ["cs_id", "configuracao", "ferramenta", "variantes_detectadas",
                                  "variantes", "resultado"], agregadas)
    escrever("avisos-novos.csv", ["cs_id", "variante", "ferramenta", "configuracao", "regra", "arquivo",
                                  "linha", "mensagem"], novos_todos)
    cont = Counter((a["ferramenta"], a["configuracao"]) for a in base)
    escrever("linha-de-base.csv", ["ferramenta", "configuracao", "avisos", "saida_ok"],
             [{"ferramenta": f, "configuracao": c, "avisos": cont[(f, c)],
               "saida_ok": estado_base.get(f"{f}/{c}", {}).get("saida_ok", False)}
              for f in FERRAMENTAS for c in CONFIGURACOES])
    resumo = Counter((l["configuracao"], l["ferramenta"], l["detectado"]) for l in linhas_var)
    for c in CONFIGURACOES:
        print(c, {f: {d: resumo[(c, f, d)] for d in ("Sim", "Não", "erro") if resumo[(c, f, d)]}
                  for f in FERRAMENTAS})


def escrever(nome, campos, linhas):
    with open(RESULTADOS / nome, "w", newline="", encoding="utf-8") as f:
        w = csv.DictWriter(f, fieldnames=campos, lineterminator="\n")
        w.writeheader()
        w.writerows(linhas)


if __name__ == "__main__":
    main()
