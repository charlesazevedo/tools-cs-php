"""Funções e dados comuns aos geradores das variantes de métricas (C11–C23).

Não é executado diretamente: é importado por c11.py, c12.py, c14.py, c15.py,
c16.py, c17.py, c22.py e c23.py. Só usa a biblioteca padrão.
"""

import random
from pathlib import Path

EXEMPLOS = Path(__file__).resolve().parents[1]


def escrever(caminho, linhas):
    """Grava as linhas (sem '\\n') em caminho, criando as pastas."""
    caminho = Path(caminho)
    caminho.parent.mkdir(parents=True, exist_ok=True)
    caminho.write_text("\n".join(linhas) + "\n", encoding="utf-8")


def segmentos(expr):
    """Divide a expressão nos '->' de nível zero (fora de parênteses/colchetes)."""
    partes, atual, prof, i = [], "", 0, 0
    while i < len(expr):
        c = expr[i]
        if c in "([":
            prof += 1
        elif c in ")]":
            prof -= 1
        if prof == 0 and expr.startswith("->", i) and atual:
            partes.append(atual)
            atual = "->"
            i += 2
            continue
        atual += c
        i += 1
    partes.append(atual)
    return partes


def quebrar(prefixo, expr, sufixo, recuo, limite=80):
    """Monta prefixo + expr + sufixo em uma ou mais linhas de até `limite`.

    Se não couber numa linha, a primeira linha leva quantos segmentos couberem
    e cada segmento restante ('->...') vai numa linha de continuação, com
    `recuo` + 4 espaços (encadeamento no estilo PSR-12). Falha se não couber.
    """
    uma = prefixo + expr + sufixo
    if len(uma) <= limite:
        return [uma]
    partes = segmentos(expr)
    linhas = [prefixo + partes[0]]
    k = 1
    while k < len(partes) - 1 and len(linhas[0] + partes[k]) <= limite:
        linhas[0] += partes[k]
        k += 1
    for p in partes[k:]:
        linhas.append(" " * (recuo + 4) + p)
    linhas[-1] += sufixo
    for l in linhas:
        if len(l) > limite:
            raise ValueError(f"linha com {len(l)} caracteres: {l!r}")
    return linhas


def docblock(descricao, params=(), retorno=None, recuo=4, var=None):
    """Comentário de documentação: descrição, @param alinhados, @return/@var."""
    r = " " * recuo
    linhas = [r + "/**"]
    import textwrap
    for d in descricao:
        for parte in textwrap.wrap(d, 80 - len(r + " * ")) or [""]:
            linhas.append(r + " * " + parte)
    if params or retorno or var:
        linhas.append(r + " *")
    if params:
        lt = max(len(t) for t, _, _ in params)
        ln = max(len(n) for _, n, _ in params)
        for t, n, d in params:
            linhas.append(r + f" * @param {t.ljust(lt)} {n.ljust(ln)} {d}".rstrip())
        if retorno:
            linhas.append(r + " *")
    if retorno:
        linhas.append(r + f" * @return {retorno}")
    if var:
        linhas.append(r + f" * @var {var}")
    linhas.append(r + " */")
    return linhas


def ncloc(linhas):
    """Linhas de código: não vazias e que não são só comentário."""
    n, em_bloco = 0, False
    for l in linhas:
        s = l.strip()
        if em_bloco:
            if "*/" in s:
                em_bloco = False
            continue
        if not s or s.startswith("//"):
            continue
        if s.startswith("/*"):
            if "*/" not in s:
                em_bloco = True
            continue
        n += 1
    return n


def extensao(linhas, inicio_prefixo):
    """Número de linhas da primeira linha que começa com inicio_prefixo até a
    chave que fecha o bloco (contando as duas), por contagem de chaves."""
    for i, l in enumerate(linhas):
        if l.lstrip().startswith(inicio_prefixo):
            prof, aberto = 0, False
            for j in range(i, len(linhas)):
                prof += linhas[j].count("{") - linhas[j].count("}")
                if "{" in linhas[j]:
                    aberto = True
                if aberto and prof == 0:
                    return j - i + 1
    raise ValueError(inicio_prefixo)


def remover_exato(tamanhos, excesso):
    """Escolhe índices de `tamanhos` cuja soma é exatamente `excesso`,
    preferindo os de índice maior (os últimos). Devolve um conjunto."""
    n = len(tamanhos)
    pode = [set() for _ in range(n + 1)]
    pode[n] = {0}
    for i in range(n - 1, -1, -1):
        pode[i] = pode[i + 1] | {s + tamanhos[i] for s in pode[i + 1] if s + tamanhos[i] <= excesso}
    if excesso not in pode[0]:
        raise ValueError(f"não há combinação que remova exatamente {excesso} linhas")
    # percorre do fim para o começo, preferindo remover os últimos
    escolhidos, resto = set(), excesso
    # reconstrução: precisamos de pode[i] = somas usando itens 0..i-1
    antes = [set() for _ in range(n + 1)]
    antes[0] = {0}
    for i in range(n):
        antes[i + 1] = antes[i] | {s + tamanhos[i] for s in antes[i] if s + tamanhos[i] <= excesso}
    for i in range(n - 1, -1, -1):
        if resto - tamanhos[i] >= 0 and (resto - tamanhos[i]) in antes[i]:
            escolhidos.add(i)
            resto -= tamanhos[i]
    assert resto == 0
    return escolhidos


def embaralhar(lista, semente):
    """Cópia embaralhada de forma determinística (semente em texto)."""
    copia = list(lista)
    random.Random(semente).shuffle(copia)
    return copia


# ---------------------------------------------------------------------------
# Métricas sobre coleções (C11 e C12). {c} é a variável da coleção.
# (chave, rótulo, expressão, formato)
# ---------------------------------------------------------------------------

M_TRANSACOES = [
    ("quantidade", "Quantidade de vendas", "{c}->count()", "inteiro"),
    ("total", "Valor total vendido", "{c}->sum('total')", "moeda"),
    ("ticket_medio", "Ticket médio", "{c}->avg('total')", "moeda"),
    ("mediana", "Valor mediano das vendas", "{c}->median('total')", "moeda"),
    ("maior_venda", "Maior venda", "{c}->max('total')", "moeda"),
    ("menor_venda", "Menor venda", "{c}->min('total')", "moeda"),
    ("desconto_medio", "Desconto médio", "{c}->avg('desconto')", "percentual"),
    ("maior_desconto", "Maior desconto concedido", "{c}->max('desconto')", "percentual"),
    ("com_desconto", "Vendas com desconto", "{c}->where('desconto', '>', 0)->count()", "inteiro"),
    ("desconto_alto", "Vendas com desconto de 10% ou mais",
     "{c}->where('desconto', '>=', 10)->count()", "inteiro"),
    ("media_parcelas", "Parcelas por venda (média)", "{c}->avg('parcelas')", "decimal"),
    ("maior_parcelamento", "Maior parcelamento", "{c}->max('parcelas')", "inteiro"),
    ("parceladas", "Vendas parceladas", "{c}->where('parcelas', '>', 1)->count()", "inteiro"),
    ("acima_de_tres_parcelas", "Vendas em mais de 3 parcelas",
     "{c}->where('parcelas', '>', 3)->count()", "inteiro"),
    ("a_vista", "Vendas à vista", "{c}->where('parcelas', 1)->count()", "inteiro"),
    ("parcela_media", "Valor médio da parcela", "{c}->avg('valor_parcelas')", "moeda"),
    ("maior_parcela", "Maior valor de parcela", "{c}->max('valor_parcelas')", "moeda"),
    ("clientes", "Clientes distintos", "{c}->pluck('cliente')->unique()->count()", "inteiro"),
    ("dias", "Dias com venda", "{c}->pluck('data')->unique()->count()", "inteiro"),
    ("primeiro_dia", "Primeiro dia com venda", "{c}->min('data')", "data"),
    ("ultimo_dia", "Último dia com venda", "{c}->max('data')", "data"),
    ("primeira_venda", "Número da primeira venda", "{c}->min('id')", "inteiro"),
    ("ultima_venda", "Número da última venda", "{c}->max('id')", "inteiro"),
    ("primeiro_registro", "Primeira venda registrada em", "{c}->min('created_at')", "data_hora"),
    ("ultimo_registro", "Última venda registrada em", "{c}->max('created_at')", "data_hora"),
    ("acima_de_cem", "Vendas acima de R$ 100,00", "{c}->where('total', '>', 100)->count()", "inteiro"),
    ("total_acima_de_cem", "Faturamento das vendas acima de R$ 100,00",
     "{c}->where('total', '>', 100)->sum('total')", "moeda"),
    ("ate_vinte", "Vendas de até R$ 20,00", "{c}->where('total', '<=', 20)->count()", "inteiro"),
    ("em_dinheiro", "Vendas em dinheiro", "{c}->where('pagamento', 'DI')->count()", "inteiro"),
    ("no_credito", "Vendas no crédito", "{c}->where('pagamento', 'CR')->count()", "inteiro"),
    ("no_debito", "Vendas no débito", "{c}->where('pagamento', 'DE')->count()", "inteiro"),
]

M_ITENS = [
    ("linhas", "Linhas de venda", "{c}->count()", "inteiro"),
    ("unidades", "Unidades vendidas", "{c}->sum('quantidade')", "inteiro"),
    ("soma_valores", "Soma dos valores de venda", "{c}->sum('valor_venda')", "moeda"),
    ("valor_medio", "Valor de venda médio", "{c}->avg('valor_venda')", "moeda"),
    ("valor_mediano", "Valor de venda mediano", "{c}->median('valor_venda')", "moeda"),
    ("maior_valor", "Maior valor de venda", "{c}->max('valor_venda')", "moeda"),
    ("menor_valor", "Menor valor de venda", "{c}->min('valor_venda')", "moeda"),
    ("media_unidades", "Unidades por linha (média)", "{c}->avg('quantidade')", "decimal"),
    ("maior_quantidade", "Maior quantidade em uma linha", "{c}->max('quantidade')", "inteiro"),
    ("produtos", "Produtos distintos", "{c}->pluck('codigo_estoque')->unique()->count()", "inteiro"),
    ("variacoes", "Variações distintas (cor e tamanho)",
     "{c}->pluck('codigo_estoque_aux')->unique()->count()", "inteiro"),
    ("vendas", "Vendas com itens", "{c}->pluck('transacao')->unique()->count()", "inteiro"),
    ("linhas_multiplas", "Linhas com mais de uma unidade",
     "{c}->where('quantidade', '>', 1)->count()", "inteiro"),
    ("linhas_unitarias", "Linhas de uma unidade", "{c}->where('quantidade', 1)->count()", "inteiro"),
    ("unidades_multiplas", "Unidades em linhas com mais de uma",
     "{c}->where('quantidade', '>', 1)->sum('quantidade')", "inteiro"),
    ("itens_caros", "Linhas acima de R$ 50,00", "{c}->where('valor_venda', '>', 50)->count()", "inteiro"),
    ("itens_baratos", "Linhas de até R$ 10,00", "{c}->where('valor_venda', '<=', 10)->count()", "inteiro"),
    ("primeira_linha", "Primeira linha de venda", "{c}->min('id')", "inteiro"),
    ("ultima_linha", "Última linha de venda", "{c}->max('id')", "inteiro"),
]

M_SANGRIAS = [
    ("quantidade", "Retiradas", "{c}->count()", "inteiro"),
    ("total", "Total retirado", "{c}->sum('valor')", "moeda"),
    ("media", "Retirada média", "{c}->avg('valor')", "moeda"),
    ("mediana", "Retirada mediana", "{c}->median('valor')", "moeda"),
    ("maior", "Maior retirada", "{c}->max('valor')", "moeda"),
    ("menor", "Menor retirada", "{c}->min('valor')", "moeda"),
    ("dias", "Dias com retirada", "{c}->pluck('data')->unique()->count()", "inteiro"),
    ("primeiro_dia", "Primeiro dia com retirada", "{c}->min('data')", "data"),
    ("ultimo_dia", "Último dia com retirada", "{c}->max('data')", "data"),
    ("motivos", "Motivos distintos", "{c}->pluck('descricao')->unique()->count()", "inteiro"),
    ("acima_de_cem", "Retiradas acima de R$ 100,00", "{c}->where('valor', '>', 100)->count()", "inteiro"),
    ("total_acima_de_cem", "Total das retiradas acima de R$ 100,00",
     "{c}->where('valor', '>', 100)->sum('valor')", "moeda"),
    ("ate_cinquenta", "Retiradas de até R$ 50,00", "{c}->where('valor', '<=', 50)->count()", "inteiro"),
    ("primeiro_registro", "Primeira retirada registrada em", "{c}->min('created_at')", "data_hora"),
    ("ultimo_registro", "Última retirada registrada em", "{c}->max('created_at')", "data_hora"),
    ("primeira", "Número da primeira retirada", "{c}->min('id')", "inteiro"),
    ("ultima", "Número da última retirada", "{c}->max('id')", "inteiro"),
]

M_ENTRADAS = [
    ("quantidade", "Entradas", "{c}->count()", "inteiro"),
    ("total", "Total adicionado", "{c}->sum('valor')", "moeda"),
    ("media", "Entrada média", "{c}->avg('valor')", "moeda"),
    ("mediana", "Entrada mediana", "{c}->median('valor')", "moeda"),
    ("maior", "Maior entrada", "{c}->max('valor')", "moeda"),
    ("menor", "Menor entrada", "{c}->min('valor')", "moeda"),
    ("motivos", "Motivos distintos", "{c}->pluck('descricao')->unique()->count()", "inteiro"),
    ("acima_de_cem", "Entradas acima de R$ 100,00", "{c}->where('valor', '>', 100)->count()", "inteiro"),
    ("total_acima_de_cem", "Total das entradas acima de R$ 100,00",
     "{c}->where('valor', '>', 100)->sum('valor')", "moeda"),
    ("ate_cinquenta", "Entradas de até R$ 50,00", "{c}->where('valor', '<=', 50)->count()", "inteiro"),
    ("primeiro_registro", "Primeira entrada registrada em", "{c}->min('created_at')", "data_hora"),
    ("ultimo_registro", "Última entrada registrada em", "{c}->max('created_at')", "data_hora"),
    ("primeira", "Número da primeira entrada", "{c}->min('id')", "inteiro"),
    ("ultima", "Número da última entrada", "{c}->max('id')", "inteiro"),
]

M_CAIXAS = [
    ("dias", "Dias de caixa", "{c}->count()", "inteiro"),
    ("soma_iniciais", "Soma dos saldos iniciais", "{c}->sum('inicial')", "moeda"),
    ("inicial_medio", "Saldo inicial médio", "{c}->avg('inicial')", "moeda"),
    ("inicial_mediano", "Saldo inicial mediano", "{c}->median('inicial')", "moeda"),
    ("maior_inicial", "Maior saldo inicial", "{c}->max('inicial')", "moeda"),
    ("menor_inicial", "Menor saldo inicial", "{c}->min('inicial')", "moeda"),
    ("soma_finais", "Soma dos saldos finais", "{c}->sum('valor')", "moeda"),
    ("final_medio", "Saldo final médio", "{c}->avg('valor')", "moeda"),
    ("final_mediano", "Saldo final mediano", "{c}->median('valor')", "moeda"),
    ("maior_final", "Maior saldo final", "{c}->max('valor')", "moeda"),
    ("menor_final", "Menor saldo final", "{c}->min('valor')", "moeda"),
    ("primeiro_dia", "Primeiro dia de caixa", "{c}->min('data')", "data"),
    ("ultimo_dia", "Último dia de caixa", "{c}->max('data')", "data"),
    ("com_troco", "Dias com troco inicial", "{c}->where('inicial', '>', 0)->count()", "inteiro"),
    ("sem_troco", "Dias sem troco inicial", "{c}->where('inicial', 0)->count()", "inteiro"),
]

M_PRODUTOS = [
    ("quantidade", "Produtos", "{c}->count()", "inteiro"),
    ("unidades", "Unidades em estoque", "{c}->sum('estoque')", "inteiro"),
    ("media_unidades", "Unidades por produto (média)", "{c}->avg('estoque')", "decimal"),
    ("maior_estoque", "Maior estoque de um produto", "{c}->max('estoque')", "inteiro"),
    ("menor_estoque", "Menor estoque de um produto", "{c}->min('estoque')", "inteiro"),
    ("estoque_baixo", "Produtos com menos de 5 unidades", "{c}->where('estoque', '<', 5)->count()", "inteiro"),
    ("preco_medio", "Preço médio", "{c}->avg('preco')", "moeda"),
    ("preco_mediano", "Preço mediano", "{c}->median('preco')", "moeda"),
    ("maior_preco", "Maior preço", "{c}->max('preco')", "moeda"),
    ("menor_preco", "Menor preço", "{c}->min('preco')", "moeda"),
    ("custo_medio", "Custo médio", "{c}->avg('preco_custo')", "moeda"),
    ("maior_custo", "Maior custo", "{c}->max('preco_custo')", "moeda"),
    ("menor_custo", "Menor custo", "{c}->min('preco_custo')", "moeda"),
    ("lucro_medio", "Margem de lucro média", "{c}->avg('lucro')", "percentual"),
    ("maior_lucro", "Maior margem de lucro", "{c}->max('lucro')", "percentual"),
    ("menor_lucro", "Menor margem de lucro", "{c}->min('lucro')", "percentual"),
    ("categorias", "Categorias distintas", "{c}->pluck('categoria')->unique()->count()", "inteiro"),
    ("marcas", "Marcas distintas", "{c}->pluck('marca')->unique()->count()", "inteiro"),
    ("fornecedores", "Fornecedores distintos", "{c}->pluck('fornecedor')->unique()->count()", "inteiro"),
    ("unidades_medida", "Unidades de medida distintas", "{c}->pluck('unidade')->unique()->count()", "inteiro"),
    ("tecidos", "Tecidos distintos", "{c}->pluck('tecido')->filter()->unique()->count()", "inteiro"),
    ("sem_tecido", "Produtos sem tecido informado", "{c}->where('tecido', null)->count()", "inteiro"),
]

M_VARIACOES = [
    ("quantidade", "Variações cadastradas", "{c}->count()", "inteiro"),
    ("unidades", "Unidades nas variações", "{c}->sum('estoque')", "inteiro"),
    ("media_unidades", "Unidades por variação (média)", "{c}->avg('estoque')", "decimal"),
    ("mediana_unidades", "Unidades por variação (mediana)", "{c}->median('estoque')", "decimal"),
    ("maior_estoque", "Maior estoque de uma variação", "{c}->max('estoque')", "inteiro"),
    ("menor_estoque", "Menor estoque de uma variação", "{c}->min('estoque')", "inteiro"),
    ("produtos", "Produtos com variação", "{c}->pluck('codigo_estoque')->unique()->count()", "inteiro"),
    ("cores", "Cores distintas", "{c}->pluck('cor')->unique()->count()", "inteiro"),
    ("tamanhos", "Tamanhos distintos", "{c}->pluck('tamanho')->unique()->count()", "inteiro"),
    ("esgotadas", "Variações esgotadas", "{c}->where('estoque', '<=', 0)->count()", "inteiro"),
    ("disponiveis", "Variações disponíveis", "{c}->where('estoque', '>', 0)->count()", "inteiro"),
    ("estoque_baixo", "Variações com menos de 5 unidades",
     "{c}->where('estoque', '<', 5)->count()", "inteiro"),
]

M_CLIENTES = [
    ("quantidade", "Clientes", "{c}->count()", "inteiro"),
    ("masculino", "Clientes do sexo masculino", "{c}->where('sexo', 'M')->count()", "inteiro"),
    ("feminino", "Clientes do sexo feminino", "{c}->where('sexo', 'F')->count()", "inteiro"),
    ("sexo_nao_informado", "Clientes sem sexo informado", "{c}->where('sexo', 'I')->count()", "inteiro"),
    ("estados", "Estados distintos", "{c}->pluck('estado')->filter()->unique()->count()", "inteiro"),
    ("cidades", "Cidades distintas", "{c}->pluck('cidade')->filter()->unique()->count()", "inteiro"),
    ("bairros", "Bairros distintos", "{c}->pluck('bairro')->filter()->unique()->count()", "inteiro"),
    ("com_endereco", "Clientes com endereço", "{c}->pluck('endereco')->filter()->count()", "inteiro"),
    ("com_cep", "Clientes com CEP", "{c}->pluck('cep')->filter()->count()", "inteiro"),
    ("com_nascimento", "Clientes com data de nascimento",
     "{c}->pluck('nascimento')->filter()->count()", "inteiro"),
    ("primeiro_cadastro", "Primeiro cadastro em", "{c}->min('created_at')", "data_hora"),
    ("ultimo_cadastro", "Último cadastro em", "{c}->max('created_at')", "data_hora"),
    ("primeiro", "Número do primeiro cliente", "{c}->min('id')", "inteiro"),
    ("ultimo", "Número do último cliente", "{c}->max('id')", "inteiro"),
]


# ---------------------------------------------------------------------------
# Indicadores do dia com expressão fixa (C14, C15, C16, C17).
# (nome em camelCase, descrição, tipo PHPDoc, expressão)
# ---------------------------------------------------------------------------

INDICADORES = [
    ("totalEmDinheiro", "Soma das vendas do dia pagas em dinheiro.", "float",
     "Transacoes::today()->where('pagamento', 'DI')->sum('total')"),
    ("totalNoCredito", "Soma das vendas do dia no cartão de crédito.", "float",
     "Transacoes::totalCreditoDay()"),
    ("totalNoDebito", "Soma das vendas do dia no cartão de débito.", "float",
     "Transacoes::totalDebitoDay()"),
    ("totalVendido", "Soma de todas as vendas do dia.", "float",
     "Transacoes::today()->sum('total')"),
    ("quantidadeDeVendas", "Número de vendas do dia.", "int",
     "Transacoes::today()->count()"),
    ("ticketMedio", "Valor médio das vendas do dia.", "float",
     "Transacoes::today()->avg('total')"),
    ("maiorVenda", "Valor da maior venda do dia.", "float",
     "Transacoes::today()->max('total')"),
    ("menorVenda", "Valor da menor venda do dia.", "float",
     "Transacoes::today()->min('total')"),
    ("descontoMedio", "Desconto médio concedido nas vendas do dia, em %.", "float",
     "Transacoes::today()->avg('desconto')"),
    ("descontoMaximo", "Maior desconto concedido no dia, em %.", "int",
     "Transacoes::today()->max('desconto')"),
    ("vendasParceladas", "Número de vendas do dia em mais de uma parcela.", "int",
     "Transacoes::today()->where('parcelas', '>', 1)->count()"),
    ("totalParcelado", "Soma das vendas do dia em mais de uma parcela.", "float",
     "Transacoes::today()->where('parcelas', '>', 1)->sum('total')"),
    ("vendasAVista", "Número de vendas do dia em parcela única.", "int",
     "Transacoes::today()->where('parcelas', 1)->count()"),
    ("mediaDeParcelas", "Número médio de parcelas das vendas do dia.", "float",
     "Transacoes::today()->avg('parcelas')"),
    ("clientesAtendidos", "Número de clientes distintos atendidos no dia.", "int",
     "Transacoes::today()->pluck('cliente')->unique()->count()"),
    ("quantidadeNoCredito", "Número de vendas do dia no cartão de crédito.", "int",
     "Transacoes::today()->where('pagamento', 'CR')->count()"),
    ("quantidadeNoDebito", "Número de vendas do dia no cartão de débito.", "int",
     "Transacoes::today()->where('pagamento', 'DE')->count()"),
    ("quantidadeEmDinheiro", "Número de vendas do dia pagas em dinheiro.", "int",
     "Transacoes::today()->where('pagamento', 'DI')->count()"),
    ("saldoInicial", "Saldo com que o caixa do dia foi aberto.", "float",
     "Caixa::where('data', date('Y-m-d'))->value('inicial')"),
    ("saldoAtual", "Saldo atual do caixa do dia.", "float",
     "Caixa::where('data', date('Y-m-d'))->value('valor')"),
    ("caixaAberto", "Indica se o caixa do dia está aberto.", "bool",
     "Caixa::checkOpen()"),
    ("totalDeSangrias", "Soma das sangrias do dia.", "float",
     "Sangria::today()->sum('valor')"),
    ("quantidadeDeSangrias", "Número de sangrias do dia.", "int",
     "Sangria::today()->count()"),
    ("maiorSangria", "Valor da maior sangria do dia.", "float",
     "Sangria::today()->max('valor')"),
    ("totalDeEntradas", "Soma das entradas de caixa do dia.", "float",
     "Entrada_caixa::today()->sum('valor')"),
    ("quantidadeDeEntradas", "Número de entradas de caixa do dia.", "int",
     "Entrada_caixa::today()->count()"),
    ("maiorEntrada", "Valor da maior entrada de caixa do dia.", "float",
     "Entrada_caixa::today()->max('valor')"),
    ("vendasDoMes", "Soma das vendas do mês corrente.", "float",
     "Transacoes::month()->sum('total')"),
    ("quantidadeDeVendasDoMes", "Número de vendas do mês corrente.", "int",
     "Transacoes::month()->count()"),
    ("vendasDoAno", "Soma das vendas do ano corrente.", "float",
     "Transacoes::year()->sum('total')"),
    ("faturamentoAnterior", "Faturamento do ano anterior.", "float",
     "Transacoes::Faturamento(date('Y') - 1)"),
    ("creditoDoMes", "Soma das vendas do mês no cartão de crédito.", "float",
     "Transacoes::month()->where('pagamento', 'CR')->sum('total')"),
    ("debitoDoMes", "Soma das vendas do mês no cartão de débito.", "float",
     "Transacoes::month()->where('pagamento', 'DE')->sum('total')"),
    ("dinheiroDoMes", "Soma das vendas do mês pagas em dinheiro.", "float",
     "Transacoes::month()->where('pagamento', 'DI')->sum('total')"),
    ("sangriasDoMes", "Soma das sangrias do mês corrente.", "float",
     "Sangria::whereMonth('data', date('m'))->sum('valor')"),
    ("entradasDoMes", "Soma das entradas de caixa do mês corrente.", "float",
     "Entrada_caixa::query()->whereMonth('created_at', date('m'))->sum('valor')"),
    ("itensVendidosHoje", "Número de unidades vendidas no dia.", "int",
     "Venda::query()->whereIn('transacao', Transacoes::today()->pluck('id'))->sum('quantidade')"),
    ("produtosDistintosVendidos", "Número de produtos distintos vendidos no dia.", "int",
     "Venda::query()->whereIn('transacao', Transacoes::today()->pluck('id'))->distinct()"
     "->count('codigo_estoque')"),
    ("unidadesEmEstoque", "Número de unidades em estoque, somadas as variações.", "int",
     "Estoque::Total()"),
    ("valorDoEstoque", "Valor do estoque a preço de venda, já formatado.", "string",
     "Estoque::valorTotalRS()"),
    ("produtosSemEstoque", "Número de produtos sem unidades em estoque.", "int",
     "Estoque::where('estoque', '<=', 0)->count()"),
    ("margemMediaDeLucro", "Margem de lucro média dos produtos, em %.", "float",
     "Estoque::avg('lucro')"),
    ("precoMedioDeCusto", "Preço de custo médio dos produtos.", "float",
     "Estoque::avg('preco_custo')"),
    ("precoMedioDeVenda", "Preço de venda médio dos produtos.", "float",
     "Estoque::avg('preco')"),
    ("produtosCadastrados", "Número de produtos cadastrados.", "int",
     "Estoque::count()"),
    ("clientesCadastrados", "Número de clientes cadastrados.", "int",
     "Cliente::count()"),
    ("clientesNovosDoMes", "Número de clientes cadastrados no mês corrente.", "int",
     "Cliente::whereMonth('created_at', date('m'))->count()"),
    ("variacoesDisponiveis", "Número de variações (cor e tamanho) com estoque.", "int",
     "Estoque_aux::where('estoque', '>', 0)->count()"),
    ("maiorParcela", "Maior valor de parcela entre as vendas do dia.", "float",
     "Transacoes::today()->max('valor_parcelas')"),
    ("sangriasDoAno", "Soma das sangrias do ano corrente.", "float",
     "Sangria::whereYear('data', date('Y'))->sum('valor')"),
    ("entradasDoAno", "Soma das entradas de caixa do ano corrente.", "float",
     "Entrada_caixa::query()->whereYear('created_at', date('Y'))->sum('valor')"),
]

MODELOS = {
    "Transacoes": "App\\Models\\Transacoes",
    "Caixa": "App\\Models\\Caixa",
    "Sangria": "App\\Models\\Sangria",
    "Entrada_caixa": "App\\Models\\Entrada_caixa",
    "Venda": "App\\Models\\Venda",
    "Estoque": "App\\Models\\Estoque",
    "Cliente": "App\\Models\\Cliente",
    "Estoque_aux": "App\\Models\\Estoque\\Estoque_aux",
}


def usos(texto):
    """Declarações `use` dos modelos citados como `Modelo::` no texto."""
    import re
    achados = {m for m in MODELOS if re.search(r"(?<![\w\\])" + m + r"::", texto)}
    return [f"use {MODELOS[m]};" for m in sorted(achados, key=lambda m: MODELOS[m])]


def cabecalho(namespace, corpo_texto, extras=()):
    """Início do arquivo PHP: <?php, namespace e use (ordenados)."""
    uses = sorted(set(usos(corpo_texto)) | {f"use {e};" for e in extras})
    linhas = ["<?php", "", f"namespace {namespace};", ""]
    if uses:
        linhas += uses + [""]
    return linhas


PREFIXOS_ACESSO = ("get", "set", "is", "has", "with")


def conferir_nomes(nomes):
    """Nenhum nome de método pode começar com get/set/is/has/with."""
    for n in nomes:
        if n.lower().startswith(PREFIXOS_ACESSO):
            raise ValueError(f"nome com prefixo de acessor: {n}")


INDICADOR = {i[0]: i for i in INDICADORES}


def snake(nome):
    """totalEmDinheiro -> total_em_dinheiro."""
    import re
    return re.sub(r"(?<!^)([A-Z])", r"_\1", nome).lower()


def metodo_indicador(nome, visibilidade="private"):
    """Método que devolve um indicador de INDICADORES."""
    _, descricao, tipo, expr = INDICADOR[nome]
    linhas = docblock([descricao], retorno=tipo)
    linhas += [f"    {visibilidade} function {nome}()", "    {"]
    linhas += quebrar("        return ", expr, ";", 8)
    linhas += ["    }"]
    return linhas


def arquivo_de_classe(namespace, nome, descricao, membros):
    """Arquivo PHP com uma classe; `membros` é uma lista de blocos de linhas,
    separados por uma linha em branco."""
    corpo = []
    for i, bloco in enumerate(membros):
        if i:
            corpo.append("")
        corpo += bloco
    classe = docblock(descricao, recuo=0) + [f"class {nome}", "{"] + corpo + ["}"]
    return cabecalho(namespace, "\n".join(classe)) + classe
