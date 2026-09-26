"""Gera as variantes de C11 (Excessive Class Length).

    python3 experimento/exemplos/gerar/c11.py

v1: classe com 1.001 linhas; v2: classe com 2.000 linhas. A medida é o total
de linhas da declaração `class` até a chave que fecha a classe, contando
comentários e linhas em branco. A classe é um relatório gerencial: um método
público (montar) e um método privado por seção, cada um com complexidade
ciclomática 1 e menos de 100 linhas.
"""

import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from _metricas_comum import (EXEMPLOS, M_CAIXAS, M_CLIENTES, M_ENTRADAS,  # noqa: E402
                             M_ITENS, M_PRODUTOS, M_SANGRIAS, M_TRANSACOES,
                             M_VARIACOES, cabecalho, docblock, embaralhar,
                             escrever, extensao, ncloc, quebrar, remover_exato)

CLASSE = "RelatorioGerencialDeCaixa"
PERIODO = "[$inicio, $fim]"
SEM_PAGAMENTO = {"em_dinheiro", "no_credito", "no_debito"}
SEM_PARCELAS = {"parceladas", "a_vista", "acima_de_tres_parcelas", "media_parcelas",
                "maior_parcelamento", "maior_parcela", "parcela_media"}
SEM_DESCONTO = {"com_desconto", "desconto_alto", "desconto_medio", "maior_desconto"}
SEM_FAIXA = {"acima_de_cem", "total_acima_de_cem", "ate_vinte"}
SEM_UNIDADES = {"unidades", "media_unidades", "maior_estoque", "menor_estoque", "estoque_baixo"}


def transacoes(*filtros, fim="->get()"):
    return f"Transacoes::whereBetween('data', {PERIODO})" + "".join(filtros) + fim


def itens(*filtros):
    return [("$vendas", transacoes(*filtros, fim="->pluck('id')")),
            ("$itens", "Venda::whereIn('transacao', $vendas)->get()")]


# (método, título, métricas, variável, consultas, usa período, métricas excluídas)
SECOES = [
    ("secaoVendasDoPeriodo", "Vendas do período", M_TRANSACOES, "$transacoes",
     [("$transacoes", transacoes())], True, set()),
    ("secaoItensVendidos", "Itens vendidos", M_ITENS, "$itens", itens(), True, set()),
    ("secaoSangrias", "Sangrias", M_SANGRIAS, "$sangrias",
     [("$sangrias", f"Sangria::whereBetween('data', {PERIODO})->get()")], True, set()),
    ("secaoEntradas", "Entradas de caixa", M_ENTRADAS, "$entradas",
     [("$entradas", "Entrada_caixa::whereDate('created_at', '>=', $inicio)"
                    "->whereDate('created_at', '<=', $fim)->get()")], True, set()),
    ("secaoCaixas", "Aberturas de caixa", M_CAIXAS, "$caixas",
     [("$caixas", f"Caixa::whereBetween('data', {PERIODO})->get()")], True, set()),
    ("secaoEstoque", "Estoque", M_PRODUTOS, "$produtos",
     [("$produtos", "Estoque::all()")], False, set()),
    ("secaoVariacoes", "Variações de estoque (cor e tamanho)", M_VARIACOES, "$variacoes",
     [("$variacoes", "Estoque_aux::all()")], False, set()),
    ("secaoClientesCadastrados", "Clientes cadastrados", M_CLIENTES, "$clientes",
     [("$clientes", "Cliente::all()")], False, set()),
    ("secaoVendasEmDinheiro", "Vendas em dinheiro", M_TRANSACOES, "$transacoes",
     [("$transacoes", transacoes("->where('pagamento', 'DI')"))], True, SEM_PAGAMENTO),
    ("secaoVendasNoCredito", "Vendas no cartão de crédito", M_TRANSACOES, "$transacoes",
     [("$transacoes", transacoes("->where('pagamento', 'CR')"))], True, SEM_PAGAMENTO),
    ("secaoVendasNoDebito", "Vendas no cartão de débito", M_TRANSACOES, "$transacoes",
     [("$transacoes", transacoes("->where('pagamento', 'DE')"))], True, SEM_PAGAMENTO),
    ("secaoItensVendidosEmDinheiro", "Itens vendidos em dinheiro", M_ITENS, "$itens",
     itens("->where('pagamento', 'DI')"), True, set()),
    ("secaoProdutosEmFalta", "Produtos em falta", M_PRODUTOS, "$produtos",
     [("$produtos", "Estoque::where('estoque', '<=', 0)->get()")], False, SEM_UNIDADES),
    ("secaoClientesAtendidos", "Clientes atendidos no período", M_CLIENTES, "$clientes",
     [("$cpfs", transacoes(fim="->pluck('cliente')")),
      ("$clientes", "Cliente::whereIn('CPF', $cpfs)->get()")], True, set()),
    ("secaoVendasParceladas", "Vendas parceladas", M_TRANSACOES, "$transacoes",
     [("$transacoes", transacoes("->where('parcelas', '>', 1)"))], True,
     {"parceladas", "a_vista"}),
    ("secaoVendasAVista", "Vendas à vista", M_TRANSACOES, "$transacoes",
     [("$transacoes", transacoes("->where('parcelas', 1)"))], True, SEM_PARCELAS),
    ("secaoItensVendidosNoCartao", "Itens vendidos no cartão", M_ITENS, "$itens",
     itens("->whereIn('pagamento', ['CR', 'DE'])"), True, set()),
    ("secaoProdutosDisponiveis", "Produtos disponíveis", M_PRODUTOS, "$produtos",
     [("$produtos", "Estoque::where('estoque', '>', 0)->get()")], False, set()),
    ("secaoClientesNovos", "Clientes cadastrados no período", M_CLIENTES, "$clientes",
     [("$clientes", "Cliente::whereDate('created_at', '>=', $inicio)"
                    "->whereDate('created_at', '<=', $fim)->get()")], True, set()),
    ("secaoVendasComDesconto", "Vendas com desconto", M_TRANSACOES, "$transacoes",
     [("$transacoes", transacoes("->where('desconto', '>', 0)"))], True, {"com_desconto"}),
    ("secaoVendasSemDesconto", "Vendas sem desconto", M_TRANSACOES, "$transacoes",
     [("$transacoes", transacoes("->where('desconto', 0)"))], True, SEM_DESCONTO),
    ("secaoVendasAcimaDeCem", "Vendas acima de R$ 100,00", M_TRANSACOES, "$transacoes",
     [("$transacoes", transacoes("->where('total', '>', 100)"))], True, SEM_FAIXA),
    ("secaoVendasDeAteVinte", "Vendas de até R$ 20,00", M_TRANSACOES, "$transacoes",
     [("$transacoes", transacoes("->where('total', '<=', 20)"))], True, SEM_FAIXA),
]

PARAMS_PERIODO = [("string", "$inicio", "Data inicial do período (Y-m-d)."),
                  ("string", "$fim", "Data final do período (Y-m-d).")]


def linha_da_metrica(metrica, var):
    _, rotulo, expr, formato = metrica
    r = " " * 20
    linhas = [" " * 16 + "[", r + f"'rotulo' => '{rotulo}',"]
    linhas += quebrar(r + "'valor' => ", expr.format(c=var), ",", 20)
    if formato != "inteiro":
        linhas.append(r + f"'formato' => '{formato}',")
    linhas.append(" " * 16 + "],")
    return linhas


def metodo_secao(secao, metricas):
    nome, titulo, _, var, consultas, periodo, _ = secao
    linhas = docblock([f'Monta a seção "{titulo}" do relatório.'],
                      PARAMS_PERIODO if periodo else (), "array")
    assinatura = f"($inicio, $fim)" if periodo else "()"
    linhas += [f"    private function {nome}{assinatura}", "    {"]
    for v, expr in consultas:
        linhas += quebrar(f"        {v} = ", expr, ";", 8)
    linhas += ["", "        return [", f"            'titulo' => '{titulo}',",
               "            'linhas' => ["]
    for m in metricas:
        linhas += linha_da_metrica(m, var)
    linhas += ["            ],", "        ];", "    }"]
    return linhas


def metricas_da_secao(secao):
    nome, _, pool, _, _, _, exclui = secao
    metricas = [m for m in pool if m[0] not in exclui]
    # a contagem (primeira métrica) abre a seção; as demais vêm embaralhadas
    return metricas[:1] + embaralhar(metricas[1:], nome)


def montar_classe(namespace, secoes, limites):
    """limites[i] = número de métricas da seção i (as primeiras da ordem)."""
    corpo = []
    corpo += docblock(["Monta todas as seções do relatório gerencial de caixa."],
                      PARAMS_PERIODO, "array")
    corpo += ["    public function montar($inicio, $fim)", "    {", "        return ["]
    for s in secoes:
        corpo.append(f"            $this->{s[0]}(" + ("$inicio, $fim" if s[5] else "") + "),")
    corpo += ["        ];", "    }"]
    tamanhos = []
    for s, n in zip(secoes, limites):
        metodo = metodo_secao(s, metricas_da_secao(s)[:n])
        tamanhos.append(len(metodo))
        corpo += [""] + metodo
    classe = docblock(["Relatório gerencial de caixa: reúne, em seções, os indicadores",
                       "de vendas, itens, sangrias, entradas, caixas, estoque e clientes."],
                      recuo=0)
    classe += [f"class {CLASSE}", "{"] + corpo + ["}"]
    return cabecalho(namespace, "\n".join(classe)) + classe, tamanhos


def tamanhos_das_linhas(secao, n):
    return [len(linha_da_metrica(m, secao[3])) for m in metricas_da_secao(secao)[:n]]


def gerar(variante, alvo, n_secoes):
    namespace = f"App\\Experimento\\C11\\{variante.upper()}"
    secoes = SECOES[:n_secoes]
    maximos = [len(metricas_da_secao(s)) for s in secoes]
    # menor teto de métricas por seção que alcança o alvo
    for teto in range(1, 40):
        limites = [min(teto, m) for m in maximos]
        linhas, _ = montar_classe(namespace, secoes, limites)
        if extensao(linhas, f"class {CLASSE}") >= alvo:
            break
    excesso = extensao(linhas, f"class {CLASSE}") - alvo
    # remove a última métrica de algumas seções (no máximo uma por seção)
    ultimas = [tamanhos_das_linhas(s, n)[-1] for s, n in zip(secoes, limites)]
    for i in remover_exato(ultimas, excesso):
        limites[i] -= 1
    linhas, tamanhos = montar_classe(namespace, secoes, limites)
    total = extensao(linhas, f"class {CLASSE}")
    assert total == alvo, (total, alvo)
    pasta = EXEMPLOS / "C11-excessive-class-length" / variante / "arquivos" / "app" / \
        "Experimento" / "C11" / variante.upper()
    escrever(pasta / f"{CLASSE}.php", linhas)
    maior_metodo = max(extensao(linhas, f"private function {s[0]}(") for s in secoes)
    print(f"C11/{variante}: classe com {total} linhas ({ncloc(linhas)} linhas de código "
          f"no arquivo), {len(secoes) + 1} métodos (1 público), maior método com "
          f"{maior_metodo} linhas, {sum(limites)} linhas de relatório")


if __name__ == "__main__":
    gerar("v1", 1001, 11)
    gerar("v2", 2000, 23)
