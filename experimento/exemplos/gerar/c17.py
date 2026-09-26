"""Gera as variantes de C17 (Excessive Public Count).

    python3 experimento/exemplos/gerar/c17.py

v1: 46 membros públicos (36 atributos + 10 métodos); v2: 90 (80 atributos +
10 métodos). Com no máximo 10 métodos públicos, a variante fica fora de C15
(mais de 10 métodos públicos) e de C14; o único outro CS atingido é C16
(mais de 15 campos), inevitável. Cada método público carregarX() preenche um
grupo de atributos públicos e devolve $this. Na v2, 29 atributos guardam o
valor já formatado de outro atributo (lido pelo método), formatado por dois
métodos privados (moeda e percentual), que não contam como membros públicos.
"""

import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from _metricas_comum import (EXEMPLOS, INDICADOR, arquivo_de_classe,  # noqa: E402
                             conferir_nomes, docblock, escrever, quebrar)

CLASSE = "PainelPublicoDoCaixa"

# (método, descrição, indicadores)
GRUPOS_V1 = [
    ("carregarVendas", "Carrega os indicadores de vendas do dia.",
     ["totalVendido", "quantidadeDeVendas", "ticketMedio", "maiorVenda", "menorVenda",
      "clientesAtendidos"]),
    ("carregarPagamentos", "Carrega as vendas do dia por forma de pagamento.",
     ["totalEmDinheiro", "totalNoCredito", "totalNoDebito", "quantidadeNoCredito",
      "quantidadeNoDebito", "quantidadeEmDinheiro"]),
    ("carregarDescontos", "Carrega os descontos concedidos no dia.",
     ["descontoMedio", "descontoMaximo"]),
    ("carregarParcelamentos", "Carrega os parcelamentos do dia.",
     ["vendasParceladas", "totalParcelado", "vendasAVista", "mediaDeParcelas"]),
    ("carregarCaixa", "Carrega a situação do caixa do dia.",
     ["saldoInicial", "saldoAtual", "caixaAberto"]),
    ("carregarSangrias", "Carrega as sangrias do dia.",
     ["totalDeSangrias", "quantidadeDeSangrias", "maiorSangria"]),
    ("carregarEntradas", "Carrega as entradas de caixa do dia.",
     ["totalDeEntradas", "quantidadeDeEntradas", "maiorEntrada"]),
    ("carregarMes", "Carrega os indicadores do mês corrente.",
     ["vendasDoMes", "quantidadeDeVendasDoMes", "sangriasDoMes", "entradasDoMes"]),
    ("carregarPagamentosDoMes", "Carrega as vendas do mês por forma de pagamento.",
     ["creditoDoMes", "debitoDoMes", "dinheiroDoMes"]),
    ("carregarAno", "Carrega os indicadores do ano corrente e do anterior.",
     ["vendasDoAno", "faturamentoAnterior"]),
]

GRUPOS_V2 = [
    ("carregarVendas", "Carrega os indicadores de vendas e descontos do dia.",
     ["totalVendido", "quantidadeDeVendas", "ticketMedio", "maiorVenda", "menorVenda",
      "clientesAtendidos", "itensVendidosHoje", "produtosDistintosVendidos", "descontoMedio",
      "descontoMaximo"]),
    ("carregarPagamentos", "Carrega as vendas do dia por forma de pagamento.",
     ["totalEmDinheiro", "totalNoCredito", "totalNoDebito", "quantidadeNoCredito",
      "quantidadeNoDebito", "quantidadeEmDinheiro"]),
    ("carregarParcelamentos", "Carrega os parcelamentos do dia.",
     ["vendasParceladas", "totalParcelado", "vendasAVista", "mediaDeParcelas", "maiorParcela"]),
    ("carregarCaixa", "Carrega a situação do caixa do dia.",
     ["saldoInicial", "saldoAtual", "caixaAberto"]),
    ("carregarMovimentacoes", "Carrega as sangrias e as entradas de caixa do dia.",
     ["totalDeSangrias", "quantidadeDeSangrias", "maiorSangria", "totalDeEntradas",
      "quantidadeDeEntradas", "maiorEntrada"]),
    ("carregarMes", "Carrega os indicadores do mês corrente.",
     ["vendasDoMes", "quantidadeDeVendasDoMes", "sangriasDoMes", "entradasDoMes"]),
    ("carregarPagamentosDoMes", "Carrega as vendas do mês por forma de pagamento.",
     ["creditoDoMes", "debitoDoMes", "dinheiroDoMes"]),
    ("carregarAno", "Carrega os indicadores do ano corrente e do anterior.",
     ["vendasDoAno", "faturamentoAnterior", "sangriasDoAno", "entradasDoAno"]),
    ("carregarEstoque", "Carrega a situação do estoque.",
     ["unidadesEmEstoque", "valorDoEstoque", "produtosSemEstoque", "margemMediaDeLucro",
      "precoMedioDeCusto", "precoMedioDeVenda", "produtosCadastrados", "variacoesDisponiveis"]),
    ("carregarClientes", "Carrega os indicadores do cadastro de clientes.",
     ["clientesCadastrados", "clientesNovosDoMes"]),
]

# atributos da v2 que ganham uma versão formatada (nome + "Formatado")
MOEDA = ["totalEmDinheiro", "totalNoCredito", "totalNoDebito", "totalVendido", "ticketMedio",
         "maiorVenda", "menorVenda", "totalParcelado", "saldoInicial", "saldoAtual",
         "totalDeSangrias", "maiorSangria", "totalDeEntradas", "maiorEntrada", "vendasDoMes",
         "vendasDoAno", "faturamentoAnterior", "creditoDoMes", "debitoDoMes", "dinheiroDoMes",
         "sangriasDoMes", "entradasDoMes", "precoMedioDeCusto", "precoMedioDeVenda",
         "maiorParcela", "sangriasDoAno", "entradasDoAno"]
PERCENTUAL = ["descontoMedio", "margemMediaDeLucro"]


def atribuicao_formatada(nome, funcao):
    uma = f"        $this->{nome}Formatado = $this->{funcao}($this->{nome});"
    if len(uma) <= 80:
        return [uma]
    return [f"        $this->{nome}Formatado = $this->{funcao}(",
            f"            $this->{nome}", "        );"]


def gerar(variante, grupos, formatar, esperado):
    atributos, metodos, nomes_attr = [], [], []
    for metodo, descricao, indicadores in grupos:
        corpo = []
        for nome in indicadores:
            _, desc, tipo, expr = INDICADOR[nome]
            atributos.append(docblock([desc], var=tipo) + [f"    public ${nome};"])
            nomes_attr.append(nome)
            corpo += quebrar(f"        $this->{nome} = ", expr, ";", 8)
        for nome in indicadores:
            if formatar and (nome in MOEDA or nome in PERCENTUAL):
                funcao = "moeda" if nome in MOEDA else "percentual"
                desc = INDICADOR[nome][1].rstrip(".")
                atributos.append(docblock([f"{desc}, formatado para exibição."], var="string")
                                 + [f"    public ${nome}Formatado;"])
                nomes_attr.append(nome + "Formatado")
                corpo += atribuicao_formatada(nome, funcao)
        bloco = docblock([descricao], retorno="$this")
        bloco += [f"    public function {metodo}()", "    {"] + corpo
        bloco += ["", "        return $this;", "    }"]
        metodos.append(bloco)
    privados = []
    if formatar:
        privados.append(docblock(["Formata um valor em reais (1.234,56)."],
                                 [("float", "$valor", "Valor a formatar.")], "string")
                        + ["    private function moeda($valor)", "    {",
                           "        return number_format($valor, 2, ',', '.');", "    }"])
        privados.append(docblock(["Formata um percentual com uma casa decimal (12,5%)."],
                                 [("float", "$valor", "Percentual a formatar.")], "string")
                        + ["    private function percentual($valor)", "    {",
                           "        return number_format($valor, 1, ',', '.') . '%';", "    }"])
    conferir_nomes([g[0] for g in grupos] + ["moeda", "percentual"])
    assert len(set(nomes_attr)) == len(nomes_attr)
    assert len(metodos) == 10 and len(nomes_attr) + len(metodos) == esperado, len(nomes_attr)
    ns = f"App\\Experimento\\C17\\{variante.upper()}"
    linhas = arquivo_de_classe(ns, CLASSE, ["Painel do caixa com indicadores públicos, "
                                            "carregados sob demanda, por grupo."],
                               atributos + metodos + privados)
    escrever(EXEMPLOS / "C17-excessive-public-count" / variante / "arquivos" / "app" /
             "Experimento" / "C17" / variante.upper() / f"{CLASSE}.php", linhas)
    print(f"C17/{variante}: {len(nomes_attr)} atributos públicos + {len(metodos)} métodos "
          f"públicos = {len(nomes_attr) + len(metodos)}; {len(privados)} métodos privados; "
          f"{len(linhas)} linhas")


if __name__ == "__main__":
    gerar("v1", GRUPOS_V1, False, 46)
    gerar("v2", GRUPOS_V2, True, 90)
