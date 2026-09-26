"""Gera as variantes de C16 (Too Many Fields).

    python3 experimento/exemplos/gerar/c16.py

v1: classe com 16 campos; v2: 30 campos. Todos são propriedades privadas de
instância (sem tipo declarado, PHP 7.0), preenchidas no construtor e lidas em
paraArray().
"""

import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))
from _metricas_comum import (EXEMPLOS, INDICADORES, arquivo_de_classe,  # noqa: E402
                             docblock, escrever, quebrar, snake)

CLASSE = "ResumoDoFechamento"


def gerar(variante, n):
    indicadores = INDICADORES[:n]
    membros = []
    for nome, descricao, tipo, _ in indicadores:
        membros.append(docblock([descricao], var=tipo) + [f"    private ${nome};"])
    construtor = docblock(["Carrega os indicadores do fechamento do caixa do dia."])
    construtor += ["    public function __construct()", "    {"]
    for nome, _, _, expr in indicadores:
        construtor += quebrar(f"        $this->{nome} = ", expr, ";", 8)
    construtor += ["    }"]
    para_array = docblock(["Devolve os indicadores do fechamento, para a view ou para JSON."],
                          retorno="array")
    para_array += ["    public function paraArray()", "    {", "        return ["]
    for nome, _, _, _ in indicadores:
        para_array.append(f"            '{snake(nome)}' => $this->{nome},")
    para_array += ["        ];", "    }"]
    membros += [construtor, para_array]
    ns = f"App\\Experimento\\C16\\{variante.upper()}"
    linhas = arquivo_de_classe(ns, CLASSE, ["Resumo do fechamento do caixa do dia."], membros)
    escrever(EXEMPLOS / "C16-too-many-fields" / variante / "arquivos" / "app" / "Experimento" /
             "C16" / variante.upper() / f"{CLASSE}.php", linhas)
    print(f"C16/{variante}: {n} campos privados, 2 métodos; {len(linhas)} linhas")


if __name__ == "__main__":
    gerar("v1", 16)
    gerar("v2", 30)
