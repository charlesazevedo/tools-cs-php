"""Gera as variantes de C12 (Excessive Method Length).

    python3 experimento/exemplos/gerar/c12.py

v1: método com 101 linhas; v2: método com 200 linhas. A medida é o total de
linhas da linha da declaração (`public function`) até a chave que fecha o
método, contando linhas em branco (o comentário de documentação fica fora).
O método (painelDetalhadoDoDia, tipo membros, inserido na CaixaController)
não tem desvios: complexidade ciclomática 1.
"""

import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from _metricas_comum import (EXEMPLOS, M_ENTRADAS, M_ITENS, M_PRODUTOS,  # noqa: E402
                             M_SANGRIAS, M_TRANSACOES, docblock, escrever,
                             extensao, ncloc, quebrar)

METODO = "painelDetalhadoDoDia"
LIMITE = 100  # a CaixaController já tem linhas mais longas; 100 evita quebrar number_format

M_CAIXA = [
    ("aberto", "", "Caixa::checkOpen()", "inteiro"),
    ("saldo_inicial", "", "Caixa::where('data', date('Y-m-d'))->value('inicial')", "moeda"),
    ("saldo_atual", "", "Caixa::where('data', date('Y-m-d'))->value('valor')", "moeda"),
]

# (chave no JSON, prefixo das variáveis, métricas, variável da coleção)
GRUPOS = [
    ("vendas", "vendas", M_TRANSACOES, "$transacoes"),
    ("caixa", "caixa", M_CAIXA, ""),
    ("itens", "itens", M_ITENS, "$itens"),
    ("sangrias", "sangrias", M_SANGRIAS, "$sangrias"),
    ("entradas", "entradas", M_ENTRADAS, "$entradas"),
    ("estoque", "estoque", M_PRODUTOS, "$produtos"),
]

CONSULTAS = [
    ("$transacoes", "Transacoes::today()"),
    ("$itens", "Venda::whereIn('transacao', $transacoes->pluck('id'))->get()"),
    ("$sangrias", "Sangria::today()"),
    ("$entradas", "Entrada_caixa::today()"),
    ("$produtos", "Estoque::all()"),
]


def camel(prefixo, chave):
    return "$" + prefixo + "".join(p.capitalize() for p in chave.split("_"))


def json_da_metrica(grupo, metrica):
    chave, _, expr, formato = metrica
    r = " " * 16
    if formato == "moeda":
        v = camel(grupo[1], chave)
        return [r + f"'{chave}' => [",
                r + f"    'valor' => {v},",
                r + f"    'formatado' => number_format({v}, 2, ',', '.'),",
                r + "],"]
    return quebrar(r + f"'{chave}' => ", expr.format(c=grupo[3]), ",", 16, LIMITE)


def variavel_da_metrica(grupo, metrica):
    chave, _, expr, formato = metrica
    if formato != "moeda":
        return []
    return quebrar(f"        {camel(grupo[1], chave)} = ", expr.format(c=grupo[3]), ";", 8,
                   LIMITE)


def montar(limites, descricao_variante):
    variaveis, json = [], []
    for g, n in zip(GRUPOS, limites):
        if n == 0:
            continue
        json.append(f"            '{g[0]}' => [")
        for m in g[2][:n]:
            variaveis += variavel_da_metrica(g, m)
            json += json_da_metrica(g, m)
        json.append("            ],")
    usadas = "\n".join(variaveis + json)
    corpo = []
    for v, expr in CONSULTAS:
        if v + "->" in usadas or v + ")" in usadas or (v == "$transacoes" and "$itens" in usadas):
            corpo += quebrar(f"        {v} = ", expr, ";", 8, LIMITE)
    corpo += variaveis
    corpo += ["", "        return response()->json([", "            'data' => date('d/m/Y'),"]
    corpo += json + ["        ]);"]
    linhas = ["<?php", "", "/**", f" * Fragmento da variante {descricao_variante}.", " */",
              "class Fragmento", "{"]
    linhas += docblock(["Monta o painel detalhado do caixa do dia (vendas, caixa, itens",
                        "vendidos, sangrias, entradas e estoque), com os valores em reais",
                        "também formatados para exibição."],
                       retorno="\\Illuminate\\Http\\JsonResponse")
    linhas += [f"    public function {METODO}()", "    {"] + corpo + ["    }", "}"]
    return linhas


def tamanho_da_metrica(g, m):
    return len(json_da_metrica(g, m)) + len(variavel_da_metrica(g, m))


def gerar(variante, alvo, n_grupos):
    rotulo = f"C12/{variante}: método de {alvo} linhas ({METODO})"
    maximos = [len(g[2]) if i < n_grupos else 0 for i, g in enumerate(GRUPOS)]
    for teto in range(1, 60):
        limites = [min(teto, m) for m in maximos]
        linhas = montar(limites, rotulo)
        if extensao(linhas, f"public function {METODO}(") >= alvo:
            break
    excesso = extensao(linhas, f"public function {METODO}(") - alvo
    # remove até 4 das últimas métricas de cada grupo, somando exatamente o excesso
    alcance = {0: []}
    for i, (g, n) in enumerate(zip(GRUPOS, limites)):
        opcoes, soma = [0], 0
        for k in range(1, min(4, n - 1) + 1):
            soma += tamanho_da_metrica(g, g[2][n - k])
            opcoes.append(soma)
        novo = {}
        for s0, ks in alcance.items():
            for k, t in enumerate(opcoes):
                if s0 + t <= excesso and s0 + t not in novo:
                    novo[s0 + t] = ks + [k]
        alcance = novo
    for i, k in enumerate(alcance[excesso]):
        limites[i] -= k
    linhas = montar(limites, rotulo)
    total = extensao(linhas, f"public function {METODO}(")
    assert total == alvo, (total, alvo)
    escrever(EXEMPLOS / "C12-excessive-method-length" / variante / "membros.php", linhas)
    ini = next(i for i, l in enumerate(linhas) if f"function {METODO}(" in l)
    print(f"C12/{variante}: método com {total} linhas ({ncloc(linhas[ini:ini + total])} "
          f"linhas de código), métricas por grupo {limites}")


if __name__ == "__main__":
    gerar("v1", 101, 6)
    gerar("v2", 200, 6)
