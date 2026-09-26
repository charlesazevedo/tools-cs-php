"""Gera as variantes de C14 (Too Many Methods).

    python3 experimento/exemplos/gerar/c14.py

v1: classe com 26 métodos (6 públicos + 20 privados); v2: 50 métodos
(10 públicos + 40 privados). Os métodos públicos agrupam indicadores; cada
indicador é um método privado de uma linha. Nenhum nome começa com
get/set/is/has/with.
"""

import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from _metricas_comum import (EXEMPLOS, arquivo_de_classe, conferir_nomes,  # noqa: E402
                             docblock, escrever, metodo_indicador, snake)

CLASSE = "PainelGerencial"

GRUPOS_V1 = [
    ("resumoDasVendas", "Resumo das vendas do dia.",
     ["totalVendido", "quantidadeDeVendas", "ticketMedio", "maiorVenda"]),
    ("resumoDosPagamentos", "Vendas do dia por forma de pagamento.",
     ["totalEmDinheiro", "totalNoCredito", "totalNoDebito"]),
    ("resumoDosParcelamentos", "Resumo dos parcelamentos do dia.",
     ["vendasParceladas", "totalParcelado", "vendasAVista"]),
    ("resumoDoCaixa", "Situação do caixa do dia.",
     ["saldoInicial", "saldoAtual", "caixaAberto"]),
    ("resumoDasMovimentacoes", "Sangrias e entradas de caixa do dia.",
     ["totalDeSangrias", "quantidadeDeSangrias", "totalDeEntradas", "quantidadeDeEntradas"]),
    ("resumoDoEstoque", "Situação do estoque.",
     ["unidadesEmEstoque", "valorDoEstoque", "produtosSemEstoque"]),
]

GRUPOS_V2 = [
    ("resumoDasVendas", "Resumo das vendas do dia.",
     ["totalVendido", "quantidadeDeVendas", "ticketMedio", "maiorVenda", "menorVenda",
      "clientesAtendidos", "itensVendidosHoje"]),
    ("resumoDosDescontos", "Descontos concedidos no dia.",
     ["descontoMedio", "descontoMaximo"]),
    ("resumoDosPagamentos", "Vendas do dia por forma de pagamento.",
     ["totalEmDinheiro", "totalNoCredito", "totalNoDebito", "quantidadeEmDinheiro"]),
    ("resumoDosParcelamentos", "Resumo dos parcelamentos do dia.",
     ["vendasParceladas", "totalParcelado", "vendasAVista", "mediaDeParcelas", "maiorParcela"]),
    ("resumoDoCaixa", "Situação do caixa do dia.",
     ["saldoInicial", "saldoAtual", "caixaAberto"]),
    ("resumoDasMovimentacoes", "Sangrias e entradas de caixa do dia.",
     ["totalDeSangrias", "quantidadeDeSangrias", "totalDeEntradas", "quantidadeDeEntradas"]),
    ("resumoDoMes", "Resumo das vendas do mês corrente.",
     ["vendasDoMes", "quantidadeDeVendasDoMes", "creditoDoMes", "debitoDoMes",
      "dinheiroDoMes"]),
    ("resumoDoAno", "Resumo do ano corrente e do anterior.",
     ["vendasDoAno", "faturamentoAnterior", "sangriasDoAno"]),
    ("resumoDoEstoque", "Situação do estoque.",
     ["unidadesEmEstoque", "valorDoEstoque", "produtosSemEstoque", "margemMediaDeLucro",
      "variacoesDisponiveis"]),
    ("resumoDosClientes", "Resumo do cadastro de clientes.",
     ["clientesCadastrados", "clientesNovosDoMes"]),
]


def metodo_publico(nome, descricao, indicadores):
    linhas = docblock([descricao], retorno="array")
    linhas += [f"    public function {nome}()", "    {", "        return ["]
    for i in indicadores:
        linhas.append(f"            '{snake(i)}' => $this->{i}(),")
    linhas += ["        ];", "    }"]
    return linhas


def gerar(variante, grupos, esperado, descricao):
    publicos = [metodo_publico(*g) for g in grupos]
    privados = [metodo_indicador(i) for g in grupos for i in g[2]]
    nomes = [g[0] for g in grupos] + [i for g in grupos for i in g[2]]
    conferir_nomes(nomes)
    assert len(set(nomes)) == len(nomes) == esperado, len(nomes)
    ns = f"App\\Experimento\\C14\\{variante.upper()}"
    linhas = arquivo_de_classe(ns, CLASSE, [descricao], publicos + privados)
    escrever(EXEMPLOS / "C14-too-many-methods" / variante / "arquivos" / "app" / "Experimento" /
             "C14" / variante.upper() / f"{CLASSE}.php", linhas)
    print(f"C14/{variante}: {len(nomes)} métodos ({len(publicos)} públicos, "
          f"{len(privados)} privados), {len(linhas)} linhas no arquivo")


if __name__ == "__main__":
    gerar("v1", GRUPOS_V1, 26, "Painel gerencial com os indicadores de vendas, caixa e estoque "
          "do dia.")
    gerar("v2", GRUPOS_V2, 50, "Painel gerencial com os indicadores de vendas e caixa do dia, "
          "do mês e do ano, do estoque e dos clientes.")
