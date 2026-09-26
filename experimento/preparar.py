"""Prepara o ambiente do experimento (L6). Idempotente: pode ser rodado de novo.

    python3 experimento/preparar.py

1. Baixa o PDVLaravel no commit fixado para experimento/trabalho/base
   (sem modificações) e instala as dependências travadas no composer.lock.
2. Sobe os contêineres (ferramentas e SonarQube) com as versões fixadas.
3. No SonarQube, cria os perfis "completos" de PHP e de HTML (sem torná-los
   padrão) com config/sonarqube/sonar-perfil-completo.sh.
4. Registra as versões efetivamente instaladas em resultados/ambiente.json.

O token do SonarQube vem de SONAR_TOKEN ou de experimento/.env (ver README).
"""

import io
import json
import os
import shutil
import tarfile
import time
import urllib.request

from comum import (BASE, COMMIT, CONTEINER, CONTROLLER, EXPERIMENTO, PERFIL_COMPLETO, PROJETO_COMPOSE,
                   REPOSITORIO, RESULTADOS, SONAR_URL, TRABALHO, no_conteiner, sh, sonar,
                   sonar_no_ar, token_sonar)


def baixar_projeto():
    marca = BASE / ".commit"
    if marca.exists() and marca.read_text().strip() == COMMIT:
        print(f"projeto base já está em {BASE}")
        return
    if BASE.exists():
        shutil.rmtree(BASE)
    url = f"https://codeload.github.com/{REPOSITORIO}/tar.gz/{COMMIT}"
    print(f"baixando {url}")
    with urllib.request.urlopen(url, timeout=300) as r:
        dados = r.read()
    TRABALHO.mkdir(parents=True, exist_ok=True)
    with tarfile.open(fileobj=io.BytesIO(dados), mode="r:gz") as tar:
        tar.extractall(TRABALHO, filter="data")
    (TRABALHO / f"PDVLaravel-{COMMIT}").rename(BASE)
    marca.write_text(COMMIT + "\n")


def subir_conteineres():
    r = sh(["docker", "compose", "-p", PROJETO_COMPOSE, "up", "-d", "--build", "ferramentas", "sonarqube"],
           cwd=EXPERIMENTO)
    if r.returncode != 0:
        raise SystemExit(r.stdout + r.stderr)


def instalar_dependencias():
    if (BASE / "vendor" / "autoload.php").exists():
        print("dependências já instaladas")
        return
    # As dependências do Laravel 5.5 pedem PHP 7.x; o vendor/ serve só para as
    # ferramentas resolverem os símbolos, então as exigências de plataforma são
    # ignoradas; os scripts e plugins do Composer não rodam (o composer.lock é
    # anterior ao allow-plugins) e os avisos de segurança do Laravel 5.5 não
    # bloqueiam a instalação: o projeto nunca é executado, só analisado.
    r = no_conteiner("composer install --no-scripts --no-plugins --no-security-blocking --no-interaction --no-progress "
                     "--ignore-platform-reqs --prefer-dist", cwd="/trabalho/base", timeout=1800)
    print(r.stdout[-2000:], r.stderr[-2000:])
    if r.returncode != 0 or not (BASE / "vendor" / "autoload.php").exists():
        raise SystemExit("composer install falhou")


def preparar_sonar():
    print("aguardando o SonarQube", end="", flush=True)
    for _ in range(180):
        if sonar_no_ar():
            break
        print(".", end="", flush=True)
        time.sleep(5)
    else:
        raise SystemExit("\nSonarQube não subiu")
    print(" ok")
    if not sonar("GET", "api/authentication/validate").get("valid"):
        raise SystemExit("token do SonarQube inválido")
    env = dict(os.environ, SONAR_TOKEN=token_sonar(), SONAR_HOST_URL=SONAR_URL, LANGUAGES="php web",
               PROFILE_NAME=PERFIL_COMPLETO, SET_DEFAULT="0", INCLUDE_DEPRECATED="1")
    r = sh(["bash", str(EXPERIMENTO / "config" / "sonarqube" / "sonar-perfil-completo.sh")], env=env)
    print(r.stdout[-3000:])
    if r.returncode != 0:
        raise SystemExit("falha ao criar os perfis completos:\n" + r.stderr[-3000:])


def registrar_ambiente():
    versoes = {}
    for nome, cmd in [("php", "php -v | head -1"),
                      ("phpmd", "php /opt/ferramentas/phpmd.phar --version"),
                      ("phpcs", "php /opt/ferramentas/phpcs.phar --version"),
                      ("phpstan", "php /opt/ferramentas/phpstan.phar --version"),
                      ("psalm", "php /opt/ferramentas/psalm.phar --version"),
                      ("composer", "composer --version 2>/dev/null")]:
        versoes[nome] = no_conteiner(cmd).stdout.strip()
    versoes["sonarqube"] = _versao_sonar()
    plugins = sonar("GET", "api/plugins/installed").get("plugins", [])
    versoes["sonarqube_plugins"] = {p["key"]: p.get("version") for p in plugins
                                    if p["key"] in ("php", "web", "javascript")}
    perfis = sonar("GET", "api/qualityprofiles/search").get("profiles", [])
    versoes["sonarqube_perfis"] = [
        {k: p.get(k) for k in ("name", "language", "isDefault", "activeRuleCount")}
        for p in perfis if p["language"] in ("php", "web")]
    imagens = {}
    for img in ["cs-experimento-ferramentas:1", "sonarqube:26.9.0.129388-community",
                "sonarsource/sonar-scanner-cli:12.2.0.4256_8.1.0"]:
        r = sh(["docker", "image", "inspect", img, "--format", "{{json .RepoDigests}} {{.Id}}"])
        imagens[img] = r.stdout.strip()
    versoes["imagens"] = imagens
    lock = json.loads((BASE / "composer.lock").read_text(encoding="utf-8"))
    laravel = next(p["version"] for p in lock["packages"] if p["name"] == "laravel/framework")
    versoes["projeto"] = {"repositorio": REPOSITORIO, "commit": COMMIT, "laravel": laravel,
                          "php_exigido": json.loads((BASE / "composer.json").read_text())["require"]["php"]}
    r = sh(["docker", "compose", "-p", PROJETO_COMPOSE, "run", "--rm", "--entrypoint", "sonar-scanner",
            "scanner", "--version"], cwd=EXPERIMENTO)
    # Tamanho da linha de base, citado no texto: a classe que recebe as injeções
    # e as linhas de código do projeto analisado, medidas pelo SonarQube.
    fonte = (BASE / CONTROLLER).read_text(encoding="utf-8")
    versoes["projeto"]["caixacontroller"] = {"linhas": len(fonte.rstrip("\n").split("\n")),
                                            "metodos_publicos": fonte.count("public function")}
    medidas = sonar("GET", "api/measures/component", component="cs-exp-base-padrao", metricKeys="ncloc")
    versoes["projeto"]["ncloc_sonarqube"] = int(medidas["component"]["measures"][0]["value"])
    versoes["sonar_scanner"] = next((l.split("INFO", 1)[1].strip() for l in r.stdout.splitlines()
                                     if "SonarScanner CLI" in l), "")
    # Tamanho das configurações: sniffs ativos do PHPCS e regras do PHPMD
    # (lidas do XML dentro do PHAR; entradas comentadas não contam).
    versoes["phpcs_sniffs"] = {c: int(no_conteiner(
        f"php /opt/ferramentas/phpcs.phar --standard=/config/phpcs/phpcs-{c}.xml -e | grep -c '^  '").stdout)
        for c in ("padrao", "completa")}
    versoes["phpmd_regras"] = int(no_conteiner(
        "php -r '$n=0; foreach ([\"cleancode\",\"codesize\",\"controversial\",\"design\",\"naming\",\"unusedcode\"] as $r) "
        "{ $d=new DOMDocument(); $d->load(\"phar:///opt/ferramentas/phpmd.phar/src/main/resources/rulesets/$r.xml\"); "
        "foreach ($d->documentElement->childNodes as $e) "
        "if ($e->nodeName===\"rule\") $n++; } echo $n;'").stdout)
    RESULTADOS.mkdir(parents=True, exist_ok=True)
    (RESULTADOS / "ambiente.json").write_text(json.dumps(versoes, indent=2, ensure_ascii=False) + "\n",
                                              encoding="utf-8")
    print(json.dumps(versoes, indent=2, ensure_ascii=False))


def _versao_sonar():
    with urllib.request.urlopen(f"{SONAR_URL}/api/server/version", timeout=10) as r:
        return r.read().decode().strip()


if __name__ == "__main__":
    baixar_projeto()
    subir_conteineres()
    instalar_dependencias()
    preparar_sonar()
    registrar_ambiente()
    print(f"pronto. contêiner das ferramentas: {CONTEINER}")
