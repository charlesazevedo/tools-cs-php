"""Definições comuns aos scripts do experimento (L6): caminhos, variantes,
execução de comandos nos contêineres e acesso à Web API do SonarQube."""

import base64
import csv
import json
import os
import subprocess
import urllib.error
import urllib.parse
import urllib.request
from pathlib import Path

EXPERIMENTO = Path(__file__).resolve().parent
RAIZ = EXPERIMENTO.parent
TRABALHO = EXPERIMENTO / "trabalho"
BASE = TRABALHO / "base"                  # PDVLaravel no commit fixado, com vendor/
EXECUCOES = TRABALHO / "execucoes"        # uma pasta por execução (base ou variante)
EXEMPLOS = EXPERIMENTO / "exemplos"
RESULTADOS = EXPERIMENTO / "resultados"   # avisos normalizados (versionados)

COMMIT = "98cddb84521765c29ff7ed691d7f5ba325cc7edc"
REPOSITORIO = "Lucasczm/PDVLaravel"
CONTROLLER = "app/Http/Controllers/Admin/CaixaController.php"
REFERENCIAS = "app/Experimento/Referencias.php"   # arquivo de apoio do Psalm (não conta)
FONTES = ["app", "resources/views"]              # o que todas as ferramentas analisam

CONTEINER = "cs-exp-ferramentas"
PROJETO_COMPOSE = "cs-experimento"
SONAR_URL = os.environ.get("SONAR_URL", "http://localhost:9010")
PERFIL_COMPLETO = "Completo (todas as regras)"

FERRAMENTAS = ["PHPMD", "PHPCS", "PHPStan", "Psalm", "SonarQube"]
CONFIGURACOES = ["padrao", "completa"]


def ler_variantes():
    with open(EXEMPLOS / "variantes.csv", encoding="utf-8") as f:
        return list(csv.DictReader(f))


def pasta_da_variante(cs_id, variante):
    [pasta] = [p for p in EXEMPLOS.iterdir() if p.is_dir() and p.name.startswith(cs_id + "-")]
    return pasta / variante


def id_execucao(cs_id=None, variante=None):
    return "base" if cs_id is None else f"{cs_id}-{variante}"


def sh(cmd, **kw):
    """Roda um comando no host e devolve o CompletedProcess (sem levantar erro)."""
    return subprocess.run(cmd, capture_output=True, text=True, **kw)


def no_conteiner(comando, cwd="/trabalho", timeout=3600):
    """Roda um comando de shell no contêiner das ferramentas."""
    return sh(["docker", "exec", "-w", cwd, CONTEINER, "sh", "-c", comando], timeout=timeout)


def caminho_no_conteiner(caminho_host):
    return "/trabalho/" + Path(caminho_host).resolve().relative_to(TRABALHO.resolve()).as_posix()


# --- SonarQube -----------------------------------------------------------------

def token_sonar():
    """Lê o token do ambiente ou de experimento/.env (fora do git). Nunca imprime."""
    if os.environ.get("SONAR_TOKEN"):
        return os.environ["SONAR_TOKEN"]
    env = EXPERIMENTO / ".env"
    if env.exists():
        for linha in env.read_text(encoding="utf-8").splitlines():
            if linha.startswith("SONAR_TOKEN="):
                return linha.split("=", 1)[1].strip()
    raise SystemExit("Defina SONAR_TOKEN (variável de ambiente ou experimento/.env). Ver experimento/README.md.")


def sonar(metodo, caminho, **params):
    """Chama a Web API do SonarQube e devolve o JSON (ou {} se a resposta for vazia)."""
    dados = urllib.parse.urlencode(params).encode()
    url = f"{SONAR_URL}/{caminho}"
    if metodo == "GET":
        url += "?" + dados.decode()
        dados = None
    req = urllib.request.Request(url, data=dados, method=metodo)
    cred = base64.b64encode((token_sonar() + ":").encode()).decode()
    req.add_header("Authorization", "Basic " + cred)
    with urllib.request.urlopen(req, timeout=120) as r:
        corpo = r.read()
    return json.loads(corpo) if corpo else {}


def sonar_no_ar():
    try:
        with urllib.request.urlopen(f"{SONAR_URL}/api/system/status", timeout=10) as r:
            return json.loads(r.read()).get("status") == "UP"
    except (urllib.error.URLError, OSError, ValueError):
        return False
