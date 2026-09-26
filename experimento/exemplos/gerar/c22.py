"""Gera as variantes de C22 (Depth of Inheritance).

    python3 experimento/exemplos/gerar/c22.py

Profundidade = número de ancestrais da classe-folha (DIT de Chidamber e
Kemerer; a raiz tem profundidade 0). "Mais de 6 níveis" = profundidade >= 7.
v1: cadeia de 8 classes, folha com 7 ancestrais; v2: cadeia de 13 classes,
folha com 12 ancestrais. A raiz (Relatorio) é uma classe nova, sem pai. Cada
nível acrescenta uma seção ao relatório de fechamento do caixa.
"""

import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from _metricas_comum import EXEMPLOS, cabecalho, docblock, escrever, quebrar  # noqa: E402

RAIZ = "Relatorio"
FOLHA = ("RelatorioDeFechamento", "Relatório de fechamento do caixa do dia.",
         "saldo_final", "Caixa::where('data', $this->data)->value('valor')")

# (classe, descrição, chave da seção, expressão) — do nível 1 ao penúltimo
NIVEIS = [
    ("RelatorioDeVendas", "Relatório que acrescenta o total vendido no dia.",
     "vendas", "Transacoes::where('data', $this->data)->sum('total')"),
    ("RelatorioDeVendasEmDinheiro", "Relatório que acrescenta as vendas do dia em dinheiro.",
     "dinheiro", "Transacoes::where('data', $this->data)->where('pagamento', 'DI')"
                 "->sum('total')"),
    ("RelatorioDeCaixa", "Relatório que acrescenta o saldo inicial do caixa do dia.",
     "saldo_inicial", "Caixa::where('data', $this->data)->value('inicial')"),
    ("RelatorioDeCaixaComSangrias", "Relatório de caixa que acrescenta as sangrias do dia.",
     "sangrias", "Sangria::where('data', $this->data)->sum('valor')"),
    ("RelatorioDeCaixaComEntradas", "Relatório de caixa que acrescenta as entradas do dia.",
     "entradas", "Entrada_caixa::query()->whereDate('created_at', $this->data)->sum('valor')"),
    ("RelatorioDeCaixaComCartoes", "Relatório de caixa que acrescenta as vendas do dia no "
     "cartão (crédito e débito).",
     "cartoes", "Transacoes::where('data', $this->data)->whereIn('pagamento', ['CR', 'DE'])"
                "->sum('total')"),
]
NIVEIS_EXTRAS = [
    ("RelatorioDeCaixaComDescontos", "Relatório de caixa que acrescenta o desconto médio "
     "do dia.",
     "desconto_medio", "Transacoes::where('data', $this->data)->avg('desconto')"),
    ("RelatorioDeCaixaComParcelamentos", "Relatório de caixa que acrescenta as vendas "
     "parceladas do dia.",
     "parceladas", "Transacoes::where('data', $this->data)->where('parcelas', '>', 1)"
                   "->count()"),
    ("RelatorioDeCaixaComItens", "Relatório de caixa que acrescenta as unidades vendidas "
     "no dia.",
     "itens", ("$vendas", "Transacoes::where('data', $this->data)->pluck('id')",
               "Venda::whereIn('transacao', $vendas)->sum('quantidade')")),
    ("RelatorioDeCaixaComClientes", "Relatório de caixa que acrescenta os clientes "
     "atendidos no dia.",
     "clientes", "Transacoes::where('data', $this->data)->distinct()->count('cliente')"),
    ("RelatorioDeCaixaComEstoque", "Relatório de caixa que acrescenta as unidades em "
     "estoque.",
     "estoque", "Estoque::Total()"),
]


def raiz(ns):
    linhas = cabecalho(ns, "")
    linhas += docblock(["Relatório do caixa de um dia. Cada subclasse acrescenta uma seção; "
                        "a classe concreta define o título."], recuo=0)
    linhas += [f"abstract class {RAIZ}", "{"]
    linhas += docblock(["Data do relatório (Y-m-d)."], var="string")
    linhas += ["    protected $data;", ""]
    linhas += docblock(["Cria o relatório de uma data."],
                       [("string", "$data", "Data do relatório (Y-m-d).")])
    linhas += ["    public function __construct($data)", "    {", "        $this->data = $data;",
               "    }", ""]
    linhas += docblock(["Monta o relatório: título, data e seções."], retorno="array")
    linhas += ["    public function gerar()", "    {", "        return [",
               "            'titulo' => $this->titulo(),", "            'data' => $this->data,",
               "            'secoes' => $this->secoes(),", "        ];", "    }", ""]
    linhas += docblock(["Título do relatório."], retorno="string")
    linhas += ["    abstract protected function titulo();", ""]
    linhas += docblock(["Seções do relatório; cada subclasse acrescenta as suas."],
                       retorno="array")
    linhas += ["    protected function secoes()", "    {", "        return [];", "    }", "}"]
    return linhas


def metodo_secoes(chave, expr):
    linhas = docblock(["Acrescenta a sua seção às seções herdadas."], retorno="array")
    linhas += ["    protected function secoes()", "    {", "        $secoes = parent::secoes();"]
    if isinstance(expr, tuple):  # (variável auxiliar, expressão dela, expressão da seção)
        linhas += quebrar(f"        {expr[0]} = ", expr[1], ";", 8)
        expr = expr[2]
    linhas += quebrar(f"        $secoes['{chave}'] = ", expr, ";", 8)
    linhas += ["", "        return $secoes;", "    }"]
    return linhas


def subclasse(ns, nome, pai, descricao, chave, expr, folha=False):
    corpo = []
    if folha:
        corpo += docblock(["Título do relatório."], retorno="string")
        corpo += ["    protected function titulo()", "    {",
                  "        return 'Fechamento do caixa';", "    }", ""]
    corpo += metodo_secoes(chave, expr)
    classe = docblock([descricao], recuo=0)
    classe += [("" if folha else "abstract ") + f"class {nome} extends {pai}", "{"]
    classe += corpo + ["}"]
    return cabecalho(ns, "\n".join(classe)) + classe


def gerar(variante, niveis):
    ns = f"App\\Experimento\\C22\\{variante.upper()}"
    pasta = EXEMPLOS / "C22-depth-of-inheritance" / variante / "arquivos" / "app" / \
        "Experimento" / "C22" / variante.upper()
    escrever(pasta / f"{RAIZ}.php", raiz(ns))
    pai = RAIZ
    cadeia = [RAIZ]
    for nome, descricao, chave, expr in niveis:
        escrever(pasta / f"{nome}.php", subclasse(ns, nome, pai, descricao, chave, expr))
        pai = nome
        cadeia.append(nome)
    nome, descricao, chave, expr = FOLHA
    escrever(pasta / f"{nome}.php", subclasse(ns, nome, pai, descricao, chave, expr, True))
    cadeia.append(nome)
    print(f"C22/{variante}: {len(cadeia)} classes; a folha {nome} tem "
          f"{len(cadeia) - 1} ancestrais: {' <- '.join(cadeia)}")
    return cadeia


if __name__ == "__main__":
    gerar("v1", NIVEIS)
    gerar("v2", NIVEIS + NIVEIS_EXTRAS)
