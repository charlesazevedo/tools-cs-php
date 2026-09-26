"""Gera as variantes de C15 (Too Many Public Methods).

    python3 experimento/exemplos/gerar/c15.py

v1: classe com 11 métodos públicos; v2: 20 métodos públicos. A classe não tem
outros métodos. Cada método devolve um indicador do dia.
"""

import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from _metricas_comum import (EXEMPLOS, INDICADORES, arquivo_de_classe,  # noqa: E402
                             conferir_nomes, escrever, metodo_indicador)

CLASSE = "ConsultasDoCaixa"


def gerar(variante, n):
    nomes = [i[0] for i in INDICADORES[:n]]
    conferir_nomes(nomes)
    ns = f"App\\Experimento\\C15\\{variante.upper()}"
    linhas = arquivo_de_classe(ns, CLASSE, ["Consultas rápidas sobre as vendas e o caixa do dia."],
                               [metodo_indicador(i, "public") for i in nomes])
    escrever(EXEMPLOS / "C15-too-many-public-methods" / variante / "arquivos" / "app" /
             "Experimento" / "C15" / variante.upper() / f"{CLASSE}.php", linhas)
    print(f"C15/{variante}: {n} métodos públicos, nenhum outro; {len(linhas)} linhas")


if __name__ == "__main__":
    gerar("v1", 11)
    gerar("v2", 20)
