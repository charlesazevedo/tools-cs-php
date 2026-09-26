"""Valida as variantes da L7 (estrutura, metadados e sintaxe). Não roda nenhuma
ferramenta de análise: só `php -l`.

    python3 experimento/exemplos/validar.py

Verifica:
- variantes.csv: cabeçalho, 2 ou 3 variantes por CS de dados/cs-experimento.csv,
  tipo válido, arquivos listados existentes e nada fora da lista;
- membros.php: um único `class Fragmento`, sem nomes que colidam com os membros
  da CaixaController original;
- arquivos/: só em app/Experimento/<Cxx>/<Vn>/ (namespace correspondente) ou em
  resources/views/experimento/;
- `php -l` em todo .php (views Blade incluídas: o PHP delas também é validado).

Sai com código 1 se algo falhar.
"""

import csv
import re
import subprocess
import sys
from collections import defaultdict
from pathlib import Path

RAIZ = Path(__file__).resolve().parents[2]
EXEMPLOS = RAIZ / "experimento" / "exemplos"
COLUNAS = ["cs_id", "variante", "forma", "medida", "tipo", "arquivos", "origem",
           "esperado_padrao", "esperado_completa", "observacao"]
TIPOS = {"membros", "arquivos", "membros+arquivos"}
MEMBROS_CAIXA = {"data", "iniciarCaixaView", "iniciarCaixa", "fecharCaixaView", "fecharCaixa",
                 "sangriaView", "sangriaPost", "addCaixaView", "addCaixa", "historico",
                 "historicoAPI", "historicoPrint"}
FERRAMENTAS = {"PHPMD", "PHPCS", "SonarQube", "PHPStan", "Psalm"}

falhas = []


def falha(msg):
    falhas.append(msg)


with open(RAIZ / "dados" / "cs-experimento.csv", encoding="utf-8") as f:
    cs = {r["id"]: r["nome"] for r in csv.DictReader(f)}
with open(RAIZ / "dados" / "regras.csv", encoding="utf-8") as f:
    regras = {(r["ferramenta"], r["regra"].split(" — ")[0].strip()) for r in csv.DictReader(f)}

arq_csv = EXEMPLOS / "variantes.csv"
if not arq_csv.exists():
    sys.exit("variantes.csv não existe")
with open(arq_csv, encoding="utf-8") as f:
    leitor = csv.DictReader(f)
    if leitor.fieldnames != COLUNAS:
        falha(f"cabeçalho de variantes.csv difere: {leitor.fieldnames}")
    linhas = list(leitor)

por_cs = defaultdict(list)
for l in linhas:
    por_cs[l["cs_id"]].append(l["variante"])
for cid in cs:
    n = len(por_cs.get(cid, []))
    if not 2 <= n <= 3:
        falha(f"{cid}: {n} variantes (esperado 2 ou 3)")
for cid in por_cs:
    if cid not in cs:
        falha(f"{cid}: não está em cs-experimento.csv")

pastas = {p.name.split("-")[0]: p for p in EXEMPLOS.iterdir() if p.is_dir() and re.match(r"C\d\d-", p.name)}
listados = set()
for l in linhas:
    cid, v = l["cs_id"], l["variante"]
    rot = f"{cid}/{v}"
    if l["tipo"] not in TIPOS:
        falha(f"{rot}: tipo inválido {l['tipo']!r}")
    if cid not in pastas:
        falha(f"{rot}: pasta Cxx- não encontrada")
        continue
    base = pastas[cid] / v
    arquivos = [a.strip() for a in l["arquivos"].split(";") if a.strip()]
    for a in arquivos:
        caminho = base / a
        listados.add(caminho.resolve())
        if not caminho.is_file():
            falha(f"{rot}: arquivo listado não existe: {a}")
    tem_membros = (base / "membros.php").is_file()
    tem_arquivos = (base / "arquivos").is_dir()
    if ("membros" in l["tipo"]) != tem_membros:
        falha(f"{rot}: tipo {l['tipo']} mas membros.php {'existe' if tem_membros else 'falta'}")
    if ("arquivos" in l["tipo"]) != tem_arquivos:
        falha(f"{rot}: tipo {l['tipo']} mas arquivos/ {'existe' if tem_arquivos else 'falta'}")
    for col in ("esperado_padrao", "esperado_completa"):
        for item in [x.strip() for x in l[col].split(";") if x.strip()]:
            ferr, _, regra = item.partition(":")
            if ferr not in FERRAMENTAS:
                falha(f"{rot}: {col} com ferramenta desconhecida: {item}")
            elif (ferr, regra) not in regras:
                falha(f"{rot}: {col} cita regra fora de regras.csv: {item}")
    if tem_membros:
        texto = (base / "membros.php").read_text(encoding="utf-8")
        classes = re.findall(r"^\s*(?:abstract\s+|final\s+)?class\s+(\w+)", texto, re.M)
        if classes != ["Fragmento"]:
            falha(f"{rot}: membros.php deve ter só class Fragmento (tem {classes})")
        for nome in re.findall(r"function\s+(\w+)\s*\(", texto):
            if nome in MEMBROS_CAIXA:
                falha(f"{rot}: método {nome} colide com a CaixaController")
    if tem_arquivos:
        for f in (base / "arquivos").rglob("*"):
            if not f.is_file():
                continue
            rel = f.relative_to(base / "arquivos").as_posix()
            ok_app = rel.startswith(f"app/Experimento/{cid}/{v.upper()}/")
            ok_view = rel.startswith("resources/views/experimento/")
            if not (ok_app or ok_view):
                falha(f"{rot}: arquivo fora dos locais permitidos: {rel}")
            if ok_app and f.suffix == ".php":
                ns = re.search(r"^namespace\s+([\w\\]+);", f.read_text(encoding="utf-8"), re.M)
                esperado = f"App\\Experimento\\{cid}\\{v.upper()}"
                if not ns or not ns.group(1).startswith(esperado):
                    falha(f"{rot}: namespace de {rel} deveria começar com {esperado}")
            if f.resolve() not in listados:
                falha(f"{rot}: arquivo não listado em variantes.csv: {rel}")

for php in sorted(EXEMPLOS.rglob("*.php")):
    if "gerar" in php.parts:
        continue
    r = subprocess.run(["php", "-l", str(php)], capture_output=True, text=True)
    if r.returncode != 0:
        falha(f"php -l: {php.relative_to(EXEMPLOS)}: {r.stdout.strip() or r.stderr.strip()}")

print(f"{len(linhas)} variantes de {len(por_cs)} CS; {len(falhas)} problema(s).")
for f in falhas:
    print(f"  - {f}")
sys.exit(1 if falhas else 0)
