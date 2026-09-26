"""Gera as variantes de C23 (Number of Children).

    python3 experimento/exemplos/gerar/c23.py

v1: classe abstrata FormaDePagamento com 16 subclasses diretas; v2: com 30.
Cada subclasse é uma forma de pagamento aceita no caixa (código, nome e taxa
da operadora). Nenhuma subclasse tem filhas (profundidade 1).
"""

import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from _metricas_comum import EXEMPLOS, cabecalho, docblock, escrever  # noqa: E402

BASE = "FormaDePagamento"

# (classe, código, nome, taxa em %)
FORMAS = [
    ("Dinheiro", "DI", "Dinheiro", "0.0"),
    ("CartaoDeCredito", "CR", "Cartão de crédito", "2.99"),
    ("CartaoDeDebito", "DE", "Cartão de débito", "1.49"),
    ("Pix", "PX", "Pix", "0.99"),
    ("BoletoBancario", "BO", "Boleto bancário", "1.2"),
    ("Cheque", "CH", "Cheque à vista", "0.0"),
    ("ChequePreDatado", "CP", "Cheque pré-datado", "0.0"),
    ("ValeAlimentacao", "VA", "Vale-alimentação", "5.0"),
    ("ValeRefeicao", "VR", "Vale-refeição", "5.5"),
    ("ValePresente", "VP", "Vale-presente da loja", "0.0"),
    ("Crediario", "CD", "Crediário da loja", "0.0"),
    ("TransferenciaBancaria", "TB", "Transferência bancária", "0.0"),
    ("CarteiraDigital", "CW", "Carteira digital", "1.99"),
    ("CreditoDaLoja", "CL", "Crédito de troca", "0.0"),
    ("Cashback", "CB", "Saldo de cashback", "0.0"),
    ("LinkDePagamento", "LP", "Link de pagamento", "3.49"),
    ("CartaoPrePago", "PP", "Cartão pré-pago", "1.99"),
    ("ValeCombustivel", "VC", "Vale-combustível", "4.0"),
    ("ValeCultura", "VT", "Vale-cultura", "5.0"),
    ("DebitoEmConta", "DC", "Débito em conta", "0.5"),
    ("DepositoBancario", "DP", "Depósito bancário", "0.0"),
    ("NotaPromissoria", "NP", "Nota promissória", "0.0"),
    ("Permuta", "PM", "Permuta", "0.0"),
    ("ConvenioEmpresa", "CE", "Convênio com empresa", "2.0"),
    ("PontosDeFidelidade", "PF", "Pontos de fidelidade", "0.0"),
    ("CartaoDaLoja", "CJ", "Cartão da loja", "1.0"),
    ("Criptomoeda", "CM", "Criptomoeda", "1.0"),
    ("PagamentoPorAproximacao", "AP", "Pagamento por aproximação", "1.49"),
    ("FinanciamentoBancario", "FB", "Financiamento bancário", "0.0"),
    ("CartaoDeCreditoInternacional", "CI", "Cartão de crédito internacional", "4.99"),
]


def base(ns):
    linhas = cabecalho(ns, "")
    linhas += docblock(["Forma de pagamento aceita no caixa."], recuo=0)
    linhas += [f"abstract class {BASE}", "{"]
    linhas += docblock(["Código gravado na coluna pagamento das transações."], retorno="string")
    linhas += ["    abstract public function codigo();", ""]
    linhas += docblock(["Nome da forma de pagamento, para exibição."], retorno="string")
    linhas += ["    abstract public function nome();", ""]
    linhas += docblock(["Taxa cobrada pela operadora, em % do valor."], retorno="float")
    linhas += ["    abstract public function taxa();", ""]
    linhas += docblock(["Valor que o caixa recebe depois de descontada a taxa."],
                       [("float", "$valor", "Valor da venda.")], "float")
    linhas += ["    public function valorLiquido($valor)", "    {",
               "        return round($valor * (1 - $this->taxa() / 100), 2);", "    }", "}"]
    return linhas


def filha(ns, classe, codigo, nome, taxa):
    linhas = cabecalho(ns, "")
    linhas += docblock([f"Pagamento: {nome.lower() if classe != 'Pix' else nome}."], recuo=0)
    linhas += [f"class {classe} extends {BASE}", "{"]
    linhas += docblock(["Código gravado na coluna pagamento das transações."], retorno="string")
    linhas += ["    public function codigo()", "    {", f"        return '{codigo}';", "    }", ""]
    linhas += docblock(["Nome da forma de pagamento, para exibição."], retorno="string")
    linhas += ["    public function nome()", "    {", f"        return '{nome}';", "    }", ""]
    linhas += docblock(["Taxa cobrada pela operadora, em % do valor."], retorno="float")
    linhas += ["    public function taxa()", "    {", f"        return {taxa};", "    }", "}"]
    return linhas


def gerar(variante, n):
    ns = f"App\\Experimento\\C23\\{variante.upper()}"
    pasta = EXEMPLOS / "C23-number-of-children" / variante / "arquivos" / "app" / \
        "Experimento" / "C23" / variante.upper()
    escrever(pasta / f"{BASE}.php", base(ns))
    formas = FORMAS[:n]
    assert len({f[1] for f in formas}) == n and len({f[0] for f in formas}) == n
    for f in formas:
        escrever(pasta / f"{f[0]}.php", filha(ns, *f))
    print(f"C23/{variante}: {BASE} com {n} subclasses diretas")


if __name__ == "__main__":
    gerar("v1", 16)
    gerar("v2", 30)
