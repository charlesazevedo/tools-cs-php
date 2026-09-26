<?php

namespace App\Experimento\C11\V2;

use App\Models\Caixa;
use App\Models\Cliente;
use App\Models\Entrada_caixa;
use App\Models\Estoque;
use App\Models\Estoque\Estoque_aux;
use App\Models\Sangria;
use App\Models\Transacoes;
use App\Models\Venda;

/**
 * Relatório gerencial de caixa: reúne, em seções, os indicadores
 * de vendas, itens, sangrias, entradas, caixas, estoque e clientes.
 */
class RelatorioGerencialDeCaixa
{
    /**
     * Monta todas as seções do relatório gerencial de caixa.
     *
     * @param string $inicio Data inicial do período (Y-m-d).
     * @param string $fim    Data final do período (Y-m-d).
     *
     * @return array
     */
    public function montar($inicio, $fim)
    {
        return [
            $this->secaoVendasDoPeriodo($inicio, $fim),
            $this->secaoItensVendidos($inicio, $fim),
            $this->secaoSangrias($inicio, $fim),
            $this->secaoEntradas($inicio, $fim),
            $this->secaoCaixas($inicio, $fim),
            $this->secaoEstoque(),
            $this->secaoVariacoes(),
            $this->secaoClientesCadastrados(),
            $this->secaoVendasEmDinheiro($inicio, $fim),
            $this->secaoVendasNoCredito($inicio, $fim),
            $this->secaoVendasNoDebito($inicio, $fim),
            $this->secaoItensVendidosEmDinheiro($inicio, $fim),
            $this->secaoProdutosEmFalta(),
            $this->secaoClientesAtendidos($inicio, $fim),
            $this->secaoVendasParceladas($inicio, $fim),
            $this->secaoVendasAVista($inicio, $fim),
            $this->secaoItensVendidosNoCartao($inicio, $fim),
            $this->secaoProdutosDisponiveis(),
            $this->secaoClientesNovos($inicio, $fim),
            $this->secaoVendasComDesconto($inicio, $fim),
            $this->secaoVendasSemDesconto($inicio, $fim),
            $this->secaoVendasAcimaDeCem($inicio, $fim),
            $this->secaoVendasDeAteVinte($inicio, $fim),
        ];
    }

    /**
     * Monta a seção "Vendas do período" do relatório.
     *
     * @param string $inicio Data inicial do período (Y-m-d).
     * @param string $fim    Data final do período (Y-m-d).
     *
     * @return array
     */
    private function secaoVendasDoPeriodo($inicio, $fim)
    {
        $transacoes = Transacoes::whereBetween('data', [$inicio, $fim])->get();

        return [
            'titulo' => 'Vendas do período',
            'linhas' => [
                [
                    'rotulo' => 'Quantidade de vendas',
                    'valor' => $transacoes->count(),
                ],
                [
                    'rotulo' => 'Clientes distintos',
                    'valor' => $transacoes->pluck('cliente')->unique()->count(),
                ],
                [
                    'rotulo' => 'Maior valor de parcela',
                    'valor' => $transacoes->max('valor_parcelas'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Vendas à vista',
                    'valor' => $transacoes->where('parcelas', 1)->count(),
                ],
                [
                    'rotulo' => 'Faturamento das vendas acima de R$ 100,00',
                    'valor' => $transacoes->where('total', '>', 100)
                        ->sum('total'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Maior desconto concedido',
                    'valor' => $transacoes->max('desconto'),
                    'formato' => 'percentual',
                ],
                [
                    'rotulo' => 'Vendas em dinheiro',
                    'valor' => $transacoes->where('pagamento', 'DI')->count(),
                ],
                [
                    'rotulo' => 'Dias com venda',
                    'valor' => $transacoes->pluck('data')->unique()->count(),
                ],
                [
                    'rotulo' => 'Vendas acima de R$ 100,00',
                    'valor' => $transacoes->where('total', '>', 100)->count(),
                ],
                [
                    'rotulo' => 'Vendas no crédito',
                    'valor' => $transacoes->where('pagamento', 'CR')->count(),
                ],
                [
                    'rotulo' => 'Número da última venda',
                    'valor' => $transacoes->max('id'),
                ],
                [
                    'rotulo' => 'Último dia com venda',
                    'valor' => $transacoes->max('data'),
                    'formato' => 'data',
                ],
                [
                    'rotulo' => 'Primeiro dia com venda',
                    'valor' => $transacoes->min('data'),
                    'formato' => 'data',
                ],
                [
                    'rotulo' => 'Menor venda',
                    'valor' => $transacoes->min('total'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Vendas com desconto',
                    'valor' => $transacoes->where('desconto', '>', 0)->count(),
                ],
            ],
        ];
    }

    /**
     * Monta a seção "Itens vendidos" do relatório.
     *
     * @param string $inicio Data inicial do período (Y-m-d).
     * @param string $fim    Data final do período (Y-m-d).
     *
     * @return array
     */
    private function secaoItensVendidos($inicio, $fim)
    {
        $vendas = Transacoes::whereBetween('data', [$inicio, $fim])
            ->pluck('id');
        $itens = Venda::whereIn('transacao', $vendas)->get();

        return [
            'titulo' => 'Itens vendidos',
            'linhas' => [
                [
                    'rotulo' => 'Linhas de venda',
                    'valor' => $itens->count(),
                ],
                [
                    'rotulo' => 'Produtos distintos',
                    'valor' => $itens->pluck('codigo_estoque')->unique()
                        ->count(),
                ],
                [
                    'rotulo' => 'Linhas acima de R$ 50,00',
                    'valor' => $itens->where('valor_venda', '>', 50)->count(),
                ],
                [
                    'rotulo' => 'Linhas de até R$ 10,00',
                    'valor' => $itens->where('valor_venda', '<=', 10)->count(),
                ],
                [
                    'rotulo' => 'Unidades por linha (média)',
                    'valor' => $itens->avg('quantidade'),
                    'formato' => 'decimal',
                ],
                [
                    'rotulo' => 'Valor de venda médio',
                    'valor' => $itens->avg('valor_venda'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Valor de venda mediano',
                    'valor' => $itens->median('valor_venda'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Primeira linha de venda',
                    'valor' => $itens->min('id'),
                ],
                [
                    'rotulo' => 'Menor valor de venda',
                    'valor' => $itens->min('valor_venda'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Soma dos valores de venda',
                    'valor' => $itens->sum('valor_venda'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Maior valor de venda',
                    'valor' => $itens->max('valor_venda'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Maior quantidade em uma linha',
                    'valor' => $itens->max('quantidade'),
                ],
                [
                    'rotulo' => 'Última linha de venda',
                    'valor' => $itens->max('id'),
                ],
                [
                    'rotulo' => 'Linhas de uma unidade',
                    'valor' => $itens->where('quantidade', 1)->count(),
                ],
                [
                    'rotulo' => 'Vendas com itens',
                    'valor' => $itens->pluck('transacao')->unique()->count(),
                ],
            ],
        ];
    }

    /**
     * Monta a seção "Sangrias" do relatório.
     *
     * @param string $inicio Data inicial do período (Y-m-d).
     * @param string $fim    Data final do período (Y-m-d).
     *
     * @return array
     */
    private function secaoSangrias($inicio, $fim)
    {
        $sangrias = Sangria::whereBetween('data', [$inicio, $fim])->get();

        return [
            'titulo' => 'Sangrias',
            'linhas' => [
                [
                    'rotulo' => 'Retiradas',
                    'valor' => $sangrias->count(),
                ],
                [
                    'rotulo' => 'Retiradas acima de R$ 100,00',
                    'valor' => $sangrias->where('valor', '>', 100)->count(),
                ],
                [
                    'rotulo' => 'Total das retiradas acima de R$ 100,00',
                    'valor' => $sangrias->where('valor', '>', 100)
                        ->sum('valor'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Retirada mediana',
                    'valor' => $sangrias->median('valor'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Último dia com retirada',
                    'valor' => $sangrias->max('data'),
                    'formato' => 'data',
                ],
                [
                    'rotulo' => 'Total retirado',
                    'valor' => $sangrias->sum('valor'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Número da última retirada',
                    'valor' => $sangrias->max('id'),
                ],
                [
                    'rotulo' => 'Número da primeira retirada',
                    'valor' => $sangrias->min('id'),
                ],
                [
                    'rotulo' => 'Maior retirada',
                    'valor' => $sangrias->max('valor'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Menor retirada',
                    'valor' => $sangrias->min('valor'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Retiradas de até R$ 50,00',
                    'valor' => $sangrias->where('valor', '<=', 50)->count(),
                ],
                [
                    'rotulo' => 'Retirada média',
                    'valor' => $sangrias->avg('valor'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Motivos distintos',
                    'valor' => $sangrias->pluck('descricao')->unique()->count(),
                ],
                [
                    'rotulo' => 'Última retirada registrada em',
                    'valor' => $sangrias->max('created_at'),
                    'formato' => 'data_hora',
                ],
                [
                    'rotulo' => 'Dias com retirada',
                    'valor' => $sangrias->pluck('data')->unique()->count(),
                ],
            ],
        ];
    }

    /**
     * Monta a seção "Entradas de caixa" do relatório.
     *
     * @param string $inicio Data inicial do período (Y-m-d).
     * @param string $fim    Data final do período (Y-m-d).
     *
     * @return array
     */
    private function secaoEntradas($inicio, $fim)
    {
        $entradas = Entrada_caixa::whereDate('created_at', '>=', $inicio)
            ->whereDate('created_at', '<=', $fim)
            ->get();

        return [
            'titulo' => 'Entradas de caixa',
            'linhas' => [
                [
                    'rotulo' => 'Entradas',
                    'valor' => $entradas->count(),
                ],
                [
                    'rotulo' => 'Menor entrada',
                    'valor' => $entradas->min('valor'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Número da última entrada',
                    'valor' => $entradas->max('id'),
                ],
                [
                    'rotulo' => 'Primeira entrada registrada em',
                    'valor' => $entradas->min('created_at'),
                    'formato' => 'data_hora',
                ],
                [
                    'rotulo' => 'Número da primeira entrada',
                    'valor' => $entradas->min('id'),
                ],
                [
                    'rotulo' => 'Entradas de até R$ 50,00',
                    'valor' => $entradas->where('valor', '<=', 50)->count(),
                ],
                [
                    'rotulo' => 'Entrada média',
                    'valor' => $entradas->avg('valor'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Total adicionado',
                    'valor' => $entradas->sum('valor'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Entradas acima de R$ 100,00',
                    'valor' => $entradas->where('valor', '>', 100)->count(),
                ],
                [
                    'rotulo' => 'Maior entrada',
                    'valor' => $entradas->max('valor'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Última entrada registrada em',
                    'valor' => $entradas->max('created_at'),
                    'formato' => 'data_hora',
                ],
                [
                    'rotulo' => 'Motivos distintos',
                    'valor' => $entradas->pluck('descricao')->unique()->count(),
                ],
                [
                    'rotulo' => 'Entrada mediana',
                    'valor' => $entradas->median('valor'),
                    'formato' => 'moeda',
                ],
            ],
        ];
    }

    /**
     * Monta a seção "Aberturas de caixa" do relatório.
     *
     * @param string $inicio Data inicial do período (Y-m-d).
     * @param string $fim    Data final do período (Y-m-d).
     *
     * @return array
     */
    private function secaoCaixas($inicio, $fim)
    {
        $caixas = Caixa::whereBetween('data', [$inicio, $fim])->get();

        return [
            'titulo' => 'Aberturas de caixa',
            'linhas' => [
                [
                    'rotulo' => 'Dias de caixa',
                    'valor' => $caixas->count(),
                ],
                [
                    'rotulo' => 'Dias sem troco inicial',
                    'valor' => $caixas->where('inicial', 0)->count(),
                ],
                [
                    'rotulo' => 'Saldo final mediano',
                    'valor' => $caixas->median('valor'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Saldo inicial mediano',
                    'valor' => $caixas->median('inicial'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Menor saldo final',
                    'valor' => $caixas->min('valor'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Dias com troco inicial',
                    'valor' => $caixas->where('inicial', '>', 0)->count(),
                ],
                [
                    'rotulo' => 'Maior saldo final',
                    'valor' => $caixas->max('valor'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Menor saldo inicial',
                    'valor' => $caixas->min('inicial'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Saldo final médio',
                    'valor' => $caixas->avg('valor'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Saldo inicial médio',
                    'valor' => $caixas->avg('inicial'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Primeiro dia de caixa',
                    'valor' => $caixas->min('data'),
                    'formato' => 'data',
                ],
                [
                    'rotulo' => 'Soma dos saldos iniciais',
                    'valor' => $caixas->sum('inicial'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Último dia de caixa',
                    'valor' => $caixas->max('data'),
                    'formato' => 'data',
                ],
                [
                    'rotulo' => 'Maior saldo inicial',
                    'valor' => $caixas->max('inicial'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Soma dos saldos finais',
                    'valor' => $caixas->sum('valor'),
                    'formato' => 'moeda',
                ],
            ],
        ];
    }

    /**
     * Monta a seção "Estoque" do relatório.
     *
     * @return array
     */
    private function secaoEstoque()
    {
        $produtos = Estoque::all();

        return [
            'titulo' => 'Estoque',
            'linhas' => [
                [
                    'rotulo' => 'Produtos',
                    'valor' => $produtos->count(),
                ],
                [
                    'rotulo' => 'Menor preço',
                    'valor' => $produtos->min('preco'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Maior custo',
                    'valor' => $produtos->max('preco_custo'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Preço médio',
                    'valor' => $produtos->avg('preco'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Unidades de medida distintas',
                    'valor' => $produtos->pluck('unidade')->unique()->count(),
                ],
                [
                    'rotulo' => 'Tecidos distintos',
                    'valor' => $produtos->pluck('tecido')->filter()->unique()
                        ->count(),
                ],
                [
                    'rotulo' => 'Marcas distintas',
                    'valor' => $produtos->pluck('marca')->unique()->count(),
                ],
                [
                    'rotulo' => 'Maior preço',
                    'valor' => $produtos->max('preco'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Menor custo',
                    'valor' => $produtos->min('preco_custo'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Margem de lucro média',
                    'valor' => $produtos->avg('lucro'),
                    'formato' => 'percentual',
                ],
                [
                    'rotulo' => 'Fornecedores distintos',
                    'valor' => $produtos->pluck('fornecedor')->unique()
                        ->count(),
                ],
                [
                    'rotulo' => 'Produtos sem tecido informado',
                    'valor' => $produtos->where('tecido', null)->count(),
                ],
                [
                    'rotulo' => 'Maior estoque de um produto',
                    'valor' => $produtos->max('estoque'),
                ],
                [
                    'rotulo' => 'Maior margem de lucro',
                    'valor' => $produtos->max('lucro'),
                    'formato' => 'percentual',
                ],
                [
                    'rotulo' => 'Custo médio',
                    'valor' => $produtos->avg('preco_custo'),
                    'formato' => 'moeda',
                ],
            ],
        ];
    }

    /**
     * Monta a seção "Variações de estoque (cor e tamanho)" do relatório.
     *
     * @return array
     */
    private function secaoVariacoes()
    {
        $variacoes = Estoque_aux::all();

        return [
            'titulo' => 'Variações de estoque (cor e tamanho)',
            'linhas' => [
                [
                    'rotulo' => 'Variações cadastradas',
                    'valor' => $variacoes->count(),
                ],
                [
                    'rotulo' => 'Menor estoque de uma variação',
                    'valor' => $variacoes->min('estoque'),
                ],
                [
                    'rotulo' => 'Cores distintas',
                    'valor' => $variacoes->pluck('cor')->unique()->count(),
                ],
                [
                    'rotulo' => 'Unidades por variação (média)',
                    'valor' => $variacoes->avg('estoque'),
                    'formato' => 'decimal',
                ],
                [
                    'rotulo' => 'Produtos com variação',
                    'valor' => $variacoes->pluck('codigo_estoque')->unique()
                        ->count(),
                ],
                [
                    'rotulo' => 'Variações disponíveis',
                    'valor' => $variacoes->where('estoque', '>', 0)->count(),
                ],
                [
                    'rotulo' => 'Unidades nas variações',
                    'valor' => $variacoes->sum('estoque'),
                ],
                [
                    'rotulo' => 'Unidades por variação (mediana)',
                    'valor' => $variacoes->median('estoque'),
                    'formato' => 'decimal',
                ],
                [
                    'rotulo' => 'Maior estoque de uma variação',
                    'valor' => $variacoes->max('estoque'),
                ],
                [
                    'rotulo' => 'Tamanhos distintos',
                    'valor' => $variacoes->pluck('tamanho')->unique()->count(),
                ],
                [
                    'rotulo' => 'Variações esgotadas',
                    'valor' => $variacoes->where('estoque', '<=', 0)->count(),
                ],
                [
                    'rotulo' => 'Variações com menos de 5 unidades',
                    'valor' => $variacoes->where('estoque', '<', 5)->count(),
                ],
            ],
        ];
    }

    /**
     * Monta a seção "Clientes cadastrados" do relatório.
     *
     * @return array
     */
    private function secaoClientesCadastrados()
    {
        $clientes = Cliente::all();

        return [
            'titulo' => 'Clientes cadastrados',
            'linhas' => [
                [
                    'rotulo' => 'Clientes',
                    'valor' => $clientes->count(),
                ],
                [
                    'rotulo' => 'Clientes sem sexo informado',
                    'valor' => $clientes->where('sexo', 'I')->count(),
                ],
                [
                    'rotulo' => 'Estados distintos',
                    'valor' => $clientes->pluck('estado')->filter()->unique()
                        ->count(),
                ],
                [
                    'rotulo' => 'Clientes do sexo feminino',
                    'valor' => $clientes->where('sexo', 'F')->count(),
                ],
                [
                    'rotulo' => 'Clientes do sexo masculino',
                    'valor' => $clientes->where('sexo', 'M')->count(),
                ],
                [
                    'rotulo' => 'Cidades distintas',
                    'valor' => $clientes->pluck('cidade')->filter()->unique()
                        ->count(),
                ],
                [
                    'rotulo' => 'Último cadastro em',
                    'valor' => $clientes->max('created_at'),
                    'formato' => 'data_hora',
                ],
                [
                    'rotulo' => 'Número do primeiro cliente',
                    'valor' => $clientes->min('id'),
                ],
                [
                    'rotulo' => 'Número do último cliente',
                    'valor' => $clientes->max('id'),
                ],
                [
                    'rotulo' => 'Clientes com endereço',
                    'valor' => $clientes->pluck('endereco')->filter()->count(),
                ],
                [
                    'rotulo' => 'Bairros distintos',
                    'valor' => $clientes->pluck('bairro')->filter()->unique()
                        ->count(),
                ],
                [
                    'rotulo' => 'Primeiro cadastro em',
                    'valor' => $clientes->min('created_at'),
                    'formato' => 'data_hora',
                ],
                [
                    'rotulo' => 'Clientes com CEP',
                    'valor' => $clientes->pluck('cep')->filter()->count(),
                ],
                [
                    'rotulo' => 'Clientes com data de nascimento',
                    'valor' => $clientes->pluck('nascimento')->filter()
                        ->count(),
                ],
            ],
        ];
    }

    /**
     * Monta a seção "Vendas em dinheiro" do relatório.
     *
     * @param string $inicio Data inicial do período (Y-m-d).
     * @param string $fim    Data final do período (Y-m-d).
     *
     * @return array
     */
    private function secaoVendasEmDinheiro($inicio, $fim)
    {
        $transacoes = Transacoes::whereBetween('data', [$inicio, $fim])
            ->where('pagamento', 'DI')
            ->get();

        return [
            'titulo' => 'Vendas em dinheiro',
            'linhas' => [
                [
                    'rotulo' => 'Quantidade de vendas',
                    'valor' => $transacoes->count(),
                ],
                [
                    'rotulo' => 'Vendas com desconto',
                    'valor' => $transacoes->where('desconto', '>', 0)->count(),
                ],
                [
                    'rotulo' => 'Menor venda',
                    'valor' => $transacoes->min('total'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Maior venda',
                    'valor' => $transacoes->max('total'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Vendas em mais de 3 parcelas',
                    'valor' => $transacoes->where('parcelas', '>', 3)->count(),
                ],
                [
                    'rotulo' => 'Faturamento das vendas acima de R$ 100,00',
                    'valor' => $transacoes->where('total', '>', 100)
                        ->sum('total'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Número da última venda',
                    'valor' => $transacoes->max('id'),
                ],
                [
                    'rotulo' => 'Valor total vendido',
                    'valor' => $transacoes->sum('total'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Valor mediano das vendas',
                    'valor' => $transacoes->median('total'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Maior desconto concedido',
                    'valor' => $transacoes->max('desconto'),
                    'formato' => 'percentual',
                ],
                [
                    'rotulo' => 'Vendas com desconto de 10% ou mais',
                    'valor' => $transacoes->where('desconto', '>=', 10)
                        ->count(),
                ],
                [
                    'rotulo' => 'Parcelas por venda (média)',
                    'valor' => $transacoes->avg('parcelas'),
                    'formato' => 'decimal',
                ],
                [
                    'rotulo' => 'Valor médio da parcela',
                    'valor' => $transacoes->avg('valor_parcelas'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Vendas acima de R$ 100,00',
                    'valor' => $transacoes->where('total', '>', 100)->count(),
                ],
                [
                    'rotulo' => 'Maior parcelamento',
                    'valor' => $transacoes->max('parcelas'),
                ],
            ],
        ];
    }

    /**
     * Monta a seção "Vendas no cartão de crédito" do relatório.
     *
     * @param string $inicio Data inicial do período (Y-m-d).
     * @param string $fim    Data final do período (Y-m-d).
     *
     * @return array
     */
    private function secaoVendasNoCredito($inicio, $fim)
    {
        $transacoes = Transacoes::whereBetween('data', [$inicio, $fim])
            ->where('pagamento', 'CR')
            ->get();

        return [
            'titulo' => 'Vendas no cartão de crédito',
            'linhas' => [
                [
                    'rotulo' => 'Quantidade de vendas',
                    'valor' => $transacoes->count(),
                ],
                [
                    'rotulo' => 'Vendas com desconto de 10% ou mais',
                    'valor' => $transacoes->where('desconto', '>=', 10)
                        ->count(),
                ],
                [
                    'rotulo' => 'Maior parcelamento',
                    'valor' => $transacoes->max('parcelas'),
                ],
                [
                    'rotulo' => 'Vendas parceladas',
                    'valor' => $transacoes->where('parcelas', '>', 1)->count(),
                ],
                [
                    'rotulo' => 'Faturamento das vendas acima de R$ 100,00',
                    'valor' => $transacoes->where('total', '>', 100)
                        ->sum('total'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Primeiro dia com venda',
                    'valor' => $transacoes->min('data'),
                    'formato' => 'data',
                ],
                [
                    'rotulo' => 'Maior valor de parcela',
                    'valor' => $transacoes->max('valor_parcelas'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Dias com venda',
                    'valor' => $transacoes->pluck('data')->unique()->count(),
                ],
                [
                    'rotulo' => 'Clientes distintos',
                    'valor' => $transacoes->pluck('cliente')->unique()->count(),
                ],
                [
                    'rotulo' => 'Último dia com venda',
                    'valor' => $transacoes->max('data'),
                    'formato' => 'data',
                ],
                [
                    'rotulo' => 'Valor mediano das vendas',
                    'valor' => $transacoes->median('total'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Primeira venda registrada em',
                    'valor' => $transacoes->min('created_at'),
                    'formato' => 'data_hora',
                ],
                [
                    'rotulo' => 'Ticket médio',
                    'valor' => $transacoes->avg('total'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Número da última venda',
                    'valor' => $transacoes->max('id'),
                ],
                [
                    'rotulo' => 'Última venda registrada em',
                    'valor' => $transacoes->max('created_at'),
                    'formato' => 'data_hora',
                ],
            ],
        ];
    }

    /**
     * Monta a seção "Vendas no cartão de débito" do relatório.
     *
     * @param string $inicio Data inicial do período (Y-m-d).
     * @param string $fim    Data final do período (Y-m-d).
     *
     * @return array
     */
    private function secaoVendasNoDebito($inicio, $fim)
    {
        $transacoes = Transacoes::whereBetween('data', [$inicio, $fim])
            ->where('pagamento', 'DE')
            ->get();

        return [
            'titulo' => 'Vendas no cartão de débito',
            'linhas' => [
                [
                    'rotulo' => 'Quantidade de vendas',
                    'valor' => $transacoes->count(),
                ],
                [
                    'rotulo' => 'Ticket médio',
                    'valor' => $transacoes->avg('total'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Parcelas por venda (média)',
                    'valor' => $transacoes->avg('parcelas'),
                    'formato' => 'decimal',
                ],
                [
                    'rotulo' => 'Última venda registrada em',
                    'valor' => $transacoes->max('created_at'),
                    'formato' => 'data_hora',
                ],
                [
                    'rotulo' => 'Valor total vendido',
                    'valor' => $transacoes->sum('total'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Vendas em mais de 3 parcelas',
                    'valor' => $transacoes->where('parcelas', '>', 3)->count(),
                ],
                [
                    'rotulo' => 'Vendas acima de R$ 100,00',
                    'valor' => $transacoes->where('total', '>', 100)->count(),
                ],
                [
                    'rotulo' => 'Último dia com venda',
                    'valor' => $transacoes->max('data'),
                    'formato' => 'data',
                ],
                [
                    'rotulo' => 'Primeiro dia com venda',
                    'valor' => $transacoes->min('data'),
                    'formato' => 'data',
                ],
                [
                    'rotulo' => 'Vendas com desconto',
                    'valor' => $transacoes->where('desconto', '>', 0)->count(),
                ],
                [
                    'rotulo' => 'Maior desconto concedido',
                    'valor' => $transacoes->max('desconto'),
                    'formato' => 'percentual',
                ],
                [
                    'rotulo' => 'Número da última venda',
                    'valor' => $transacoes->max('id'),
                ],
                [
                    'rotulo' => 'Dias com venda',
                    'valor' => $transacoes->pluck('data')->unique()->count(),
                ],
                [
                    'rotulo' => 'Clientes distintos',
                    'valor' => $transacoes->pluck('cliente')->unique()->count(),
                ],
                [
                    'rotulo' => 'Maior parcelamento',
                    'valor' => $transacoes->max('parcelas'),
                ],
            ],
        ];
    }

    /**
     * Monta a seção "Itens vendidos em dinheiro" do relatório.
     *
     * @param string $inicio Data inicial do período (Y-m-d).
     * @param string $fim    Data final do período (Y-m-d).
     *
     * @return array
     */
    private function secaoItensVendidosEmDinheiro($inicio, $fim)
    {
        $vendas = Transacoes::whereBetween('data', [$inicio, $fim])
            ->where('pagamento', 'DI')
            ->pluck('id');
        $itens = Venda::whereIn('transacao', $vendas)->get();

        return [
            'titulo' => 'Itens vendidos em dinheiro',
            'linhas' => [
                [
                    'rotulo' => 'Linhas de venda',
                    'valor' => $itens->count(),
                ],
                [
                    'rotulo' => 'Última linha de venda',
                    'valor' => $itens->max('id'),
                ],
                [
                    'rotulo' => 'Valor de venda médio',
                    'valor' => $itens->avg('valor_venda'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Unidades em linhas com mais de uma',
                    'valor' => $itens->where('quantidade', '>', 1)
                        ->sum('quantidade'),
                ],
                [
                    'rotulo' => 'Menor valor de venda',
                    'valor' => $itens->min('valor_venda'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Maior valor de venda',
                    'valor' => $itens->max('valor_venda'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Vendas com itens',
                    'valor' => $itens->pluck('transacao')->unique()->count(),
                ],
                [
                    'rotulo' => 'Variações distintas (cor e tamanho)',
                    'valor' => $itens->pluck('codigo_estoque_aux')->unique()
                        ->count(),
                ],
                [
                    'rotulo' => 'Linhas de até R$ 10,00',
                    'valor' => $itens->where('valor_venda', '<=', 10)->count(),
                ],
                [
                    'rotulo' => 'Unidades vendidas',
                    'valor' => $itens->sum('quantidade'),
                ],
                [
                    'rotulo' => 'Soma dos valores de venda',
                    'valor' => $itens->sum('valor_venda'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Primeira linha de venda',
                    'valor' => $itens->min('id'),
                ],
                [
                    'rotulo' => 'Linhas de uma unidade',
                    'valor' => $itens->where('quantidade', 1)->count(),
                ],
                [
                    'rotulo' => 'Unidades por linha (média)',
                    'valor' => $itens->avg('quantidade'),
                    'formato' => 'decimal',
                ],
                [
                    'rotulo' => 'Maior quantidade em uma linha',
                    'valor' => $itens->max('quantidade'),
                ],
            ],
        ];
    }

    /**
     * Monta a seção "Produtos em falta" do relatório.
     *
     * @return array
     */
    private function secaoProdutosEmFalta()
    {
        $produtos = Estoque::where('estoque', '<=', 0)->get();

        return [
            'titulo' => 'Produtos em falta',
            'linhas' => [
                [
                    'rotulo' => 'Produtos',
                    'valor' => $produtos->count(),
                ],
                [
                    'rotulo' => 'Produtos sem tecido informado',
                    'valor' => $produtos->where('tecido', null)->count(),
                ],
                [
                    'rotulo' => 'Fornecedores distintos',
                    'valor' => $produtos->pluck('fornecedor')->unique()
                        ->count(),
                ],
                [
                    'rotulo' => 'Menor preço',
                    'valor' => $produtos->min('preco'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Menor margem de lucro',
                    'valor' => $produtos->min('lucro'),
                    'formato' => 'percentual',
                ],
                [
                    'rotulo' => 'Maior margem de lucro',
                    'valor' => $produtos->max('lucro'),
                    'formato' => 'percentual',
                ],
                [
                    'rotulo' => 'Preço médio',
                    'valor' => $produtos->avg('preco'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Unidades de medida distintas',
                    'valor' => $produtos->pluck('unidade')->unique()->count(),
                ],
                [
                    'rotulo' => 'Marcas distintas',
                    'valor' => $produtos->pluck('marca')->unique()->count(),
                ],
                [
                    'rotulo' => 'Margem de lucro média',
                    'valor' => $produtos->avg('lucro'),
                    'formato' => 'percentual',
                ],
                [
                    'rotulo' => 'Maior preço',
                    'valor' => $produtos->max('preco'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Maior custo',
                    'valor' => $produtos->max('preco_custo'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Menor custo',
                    'valor' => $produtos->min('preco_custo'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Custo médio',
                    'valor' => $produtos->avg('preco_custo'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Tecidos distintos',
                    'valor' => $produtos->pluck('tecido')->filter()->unique()
                        ->count(),
                ],
            ],
        ];
    }

    /**
     * Monta a seção "Clientes atendidos no período" do relatório.
     *
     * @param string $inicio Data inicial do período (Y-m-d).
     * @param string $fim    Data final do período (Y-m-d).
     *
     * @return array
     */
    private function secaoClientesAtendidos($inicio, $fim)
    {
        $cpfs = Transacoes::whereBetween('data', [$inicio, $fim])
            ->pluck('cliente');
        $clientes = Cliente::whereIn('CPF', $cpfs)->get();

        return [
            'titulo' => 'Clientes atendidos no período',
            'linhas' => [
                [
                    'rotulo' => 'Clientes',
                    'valor' => $clientes->count(),
                ],
                [
                    'rotulo' => 'Clientes do sexo feminino',
                    'valor' => $clientes->where('sexo', 'F')->count(),
                ],
                [
                    'rotulo' => 'Cidades distintas',
                    'valor' => $clientes->pluck('cidade')->filter()->unique()
                        ->count(),
                ],
                [
                    'rotulo' => 'Número do primeiro cliente',
                    'valor' => $clientes->min('id'),
                ],
                [
                    'rotulo' => 'Clientes com endereço',
                    'valor' => $clientes->pluck('endereco')->filter()->count(),
                ],
                [
                    'rotulo' => 'Estados distintos',
                    'valor' => $clientes->pluck('estado')->filter()->unique()
                        ->count(),
                ],
                [
                    'rotulo' => 'Clientes com CEP',
                    'valor' => $clientes->pluck('cep')->filter()->count(),
                ],
                [
                    'rotulo' => 'Último cadastro em',
                    'valor' => $clientes->max('created_at'),
                    'formato' => 'data_hora',
                ],
                [
                    'rotulo' => 'Número do último cliente',
                    'valor' => $clientes->max('id'),
                ],
                [
                    'rotulo' => 'Primeiro cadastro em',
                    'valor' => $clientes->min('created_at'),
                    'formato' => 'data_hora',
                ],
                [
                    'rotulo' => 'Clientes do sexo masculino',
                    'valor' => $clientes->where('sexo', 'M')->count(),
                ],
                [
                    'rotulo' => 'Clientes com data de nascimento',
                    'valor' => $clientes->pluck('nascimento')->filter()
                        ->count(),
                ],
                [
                    'rotulo' => 'Bairros distintos',
                    'valor' => $clientes->pluck('bairro')->filter()->unique()
                        ->count(),
                ],
                [
                    'rotulo' => 'Clientes sem sexo informado',
                    'valor' => $clientes->where('sexo', 'I')->count(),
                ],
            ],
        ];
    }

    /**
     * Monta a seção "Vendas parceladas" do relatório.
     *
     * @param string $inicio Data inicial do período (Y-m-d).
     * @param string $fim    Data final do período (Y-m-d).
     *
     * @return array
     */
    private function secaoVendasParceladas($inicio, $fim)
    {
        $transacoes = Transacoes::whereBetween('data', [$inicio, $fim])
            ->where('parcelas', '>', 1)
            ->get();

        return [
            'titulo' => 'Vendas parceladas',
            'linhas' => [
                [
                    'rotulo' => 'Quantidade de vendas',
                    'valor' => $transacoes->count(),
                ],
                [
                    'rotulo' => 'Vendas em dinheiro',
                    'valor' => $transacoes->where('pagamento', 'DI')->count(),
                ],
                [
                    'rotulo' => 'Maior parcelamento',
                    'valor' => $transacoes->max('parcelas'),
                ],
                [
                    'rotulo' => 'Dias com venda',
                    'valor' => $transacoes->pluck('data')->unique()->count(),
                ],
                [
                    'rotulo' => 'Vendas de até R$ 20,00',
                    'valor' => $transacoes->where('total', '<=', 20)->count(),
                ],
                [
                    'rotulo' => 'Valor total vendido',
                    'valor' => $transacoes->sum('total'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Última venda registrada em',
                    'valor' => $transacoes->max('created_at'),
                    'formato' => 'data_hora',
                ],
                [
                    'rotulo' => 'Número da última venda',
                    'valor' => $transacoes->max('id'),
                ],
                [
                    'rotulo' => 'Vendas em mais de 3 parcelas',
                    'valor' => $transacoes->where('parcelas', '>', 3)->count(),
                ],
                [
                    'rotulo' => 'Vendas com desconto',
                    'valor' => $transacoes->where('desconto', '>', 0)->count(),
                ],
                [
                    'rotulo' => 'Desconto médio',
                    'valor' => $transacoes->avg('desconto'),
                    'formato' => 'percentual',
                ],
                [
                    'rotulo' => 'Menor venda',
                    'valor' => $transacoes->min('total'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Faturamento das vendas acima de R$ 100,00',
                    'valor' => $transacoes->where('total', '>', 100)
                        ->sum('total'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Último dia com venda',
                    'valor' => $transacoes->max('data'),
                    'formato' => 'data',
                ],
                [
                    'rotulo' => 'Vendas acima de R$ 100,00',
                    'valor' => $transacoes->where('total', '>', 100)->count(),
                ],
            ],
        ];
    }

    /**
     * Monta a seção "Vendas à vista" do relatório.
     *
     * @param string $inicio Data inicial do período (Y-m-d).
     * @param string $fim    Data final do período (Y-m-d).
     *
     * @return array
     */
    private function secaoVendasAVista($inicio, $fim)
    {
        $transacoes = Transacoes::whereBetween('data', [$inicio, $fim])
            ->where('parcelas', 1)
            ->get();

        return [
            'titulo' => 'Vendas à vista',
            'linhas' => [
                [
                    'rotulo' => 'Quantidade de vendas',
                    'valor' => $transacoes->count(),
                ],
                [
                    'rotulo' => 'Vendas no crédito',
                    'valor' => $transacoes->where('pagamento', 'CR')->count(),
                ],
                [
                    'rotulo' => 'Maior desconto concedido',
                    'valor' => $transacoes->max('desconto'),
                    'formato' => 'percentual',
                ],
                [
                    'rotulo' => 'Primeiro dia com venda',
                    'valor' => $transacoes->min('data'),
                    'formato' => 'data',
                ],
                [
                    'rotulo' => 'Dias com venda',
                    'valor' => $transacoes->pluck('data')->unique()->count(),
                ],
                [
                    'rotulo' => 'Faturamento das vendas acima de R$ 100,00',
                    'valor' => $transacoes->where('total', '>', 100)
                        ->sum('total'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Ticket médio',
                    'valor' => $transacoes->avg('total'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Desconto médio',
                    'valor' => $transacoes->avg('desconto'),
                    'formato' => 'percentual',
                ],
                [
                    'rotulo' => 'Número da última venda',
                    'valor' => $transacoes->max('id'),
                ],
                [
                    'rotulo' => 'Vendas acima de R$ 100,00',
                    'valor' => $transacoes->where('total', '>', 100)->count(),
                ],
                [
                    'rotulo' => 'Maior venda',
                    'valor' => $transacoes->max('total'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Primeira venda registrada em',
                    'valor' => $transacoes->min('created_at'),
                    'formato' => 'data_hora',
                ],
                [
                    'rotulo' => 'Vendas no débito',
                    'valor' => $transacoes->where('pagamento', 'DE')->count(),
                ],
                [
                    'rotulo' => 'Último dia com venda',
                    'valor' => $transacoes->max('data'),
                    'formato' => 'data',
                ],
                [
                    'rotulo' => 'Número da primeira venda',
                    'valor' => $transacoes->min('id'),
                ],
            ],
        ];
    }

    /**
     * Monta a seção "Itens vendidos no cartão" do relatório.
     *
     * @param string $inicio Data inicial do período (Y-m-d).
     * @param string $fim    Data final do período (Y-m-d).
     *
     * @return array
     */
    private function secaoItensVendidosNoCartao($inicio, $fim)
    {
        $vendas = Transacoes::whereBetween('data', [$inicio, $fim])
            ->whereIn('pagamento', ['CR', 'DE'])
            ->pluck('id');
        $itens = Venda::whereIn('transacao', $vendas)->get();

        return [
            'titulo' => 'Itens vendidos no cartão',
            'linhas' => [
                [
                    'rotulo' => 'Linhas de venda',
                    'valor' => $itens->count(),
                ],
                [
                    'rotulo' => 'Linhas de uma unidade',
                    'valor' => $itens->where('quantidade', 1)->count(),
                ],
                [
                    'rotulo' => 'Linhas de até R$ 10,00',
                    'valor' => $itens->where('valor_venda', '<=', 10)->count(),
                ],
                [
                    'rotulo' => 'Linhas com mais de uma unidade',
                    'valor' => $itens->where('quantidade', '>', 1)->count(),
                ],
                [
                    'rotulo' => 'Unidades por linha (média)',
                    'valor' => $itens->avg('quantidade'),
                    'formato' => 'decimal',
                ],
                [
                    'rotulo' => 'Maior valor de venda',
                    'valor' => $itens->max('valor_venda'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Unidades em linhas com mais de uma',
                    'valor' => $itens->where('quantidade', '>', 1)
                        ->sum('quantidade'),
                ],
                [
                    'rotulo' => 'Soma dos valores de venda',
                    'valor' => $itens->sum('valor_venda'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Valor de venda médio',
                    'valor' => $itens->avg('valor_venda'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Última linha de venda',
                    'valor' => $itens->max('id'),
                ],
                [
                    'rotulo' => 'Unidades vendidas',
                    'valor' => $itens->sum('quantidade'),
                ],
                [
                    'rotulo' => 'Produtos distintos',
                    'valor' => $itens->pluck('codigo_estoque')->unique()
                        ->count(),
                ],
                [
                    'rotulo' => 'Primeira linha de venda',
                    'valor' => $itens->min('id'),
                ],
                [
                    'rotulo' => 'Linhas acima de R$ 50,00',
                    'valor' => $itens->where('valor_venda', '>', 50)->count(),
                ],
                [
                    'rotulo' => 'Menor valor de venda',
                    'valor' => $itens->min('valor_venda'),
                    'formato' => 'moeda',
                ],
            ],
        ];
    }

    /**
     * Monta a seção "Produtos disponíveis" do relatório.
     *
     * @return array
     */
    private function secaoProdutosDisponiveis()
    {
        $produtos = Estoque::where('estoque', '>', 0)->get();

        return [
            'titulo' => 'Produtos disponíveis',
            'linhas' => [
                [
                    'rotulo' => 'Produtos',
                    'valor' => $produtos->count(),
                ],
                [
                    'rotulo' => 'Maior margem de lucro',
                    'valor' => $produtos->max('lucro'),
                    'formato' => 'percentual',
                ],
                [
                    'rotulo' => 'Fornecedores distintos',
                    'valor' => $produtos->pluck('fornecedor')->unique()
                        ->count(),
                ],
                [
                    'rotulo' => 'Unidades de medida distintas',
                    'valor' => $produtos->pluck('unidade')->unique()->count(),
                ],
                [
                    'rotulo' => 'Margem de lucro média',
                    'valor' => $produtos->avg('lucro'),
                    'formato' => 'percentual',
                ],
                [
                    'rotulo' => 'Categorias distintas',
                    'valor' => $produtos->pluck('categoria')->unique()->count(),
                ],
                [
                    'rotulo' => 'Produtos com menos de 5 unidades',
                    'valor' => $produtos->where('estoque', '<', 5)->count(),
                ],
                [
                    'rotulo' => 'Menor margem de lucro',
                    'valor' => $produtos->min('lucro'),
                    'formato' => 'percentual',
                ],
                [
                    'rotulo' => 'Custo médio',
                    'valor' => $produtos->avg('preco_custo'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Maior custo',
                    'valor' => $produtos->max('preco_custo'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Menor preço',
                    'valor' => $produtos->min('preco'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Produtos sem tecido informado',
                    'valor' => $produtos->where('tecido', null)->count(),
                ],
                [
                    'rotulo' => 'Menor estoque de um produto',
                    'valor' => $produtos->min('estoque'),
                ],
                [
                    'rotulo' => 'Preço médio',
                    'valor' => $produtos->avg('preco'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Marcas distintas',
                    'valor' => $produtos->pluck('marca')->unique()->count(),
                ],
            ],
        ];
    }

    /**
     * Monta a seção "Clientes cadastrados no período" do relatório.
     *
     * @param string $inicio Data inicial do período (Y-m-d).
     * @param string $fim    Data final do período (Y-m-d).
     *
     * @return array
     */
    private function secaoClientesNovos($inicio, $fim)
    {
        $clientes = Cliente::whereDate('created_at', '>=', $inicio)
            ->whereDate('created_at', '<=', $fim)
            ->get();

        return [
            'titulo' => 'Clientes cadastrados no período',
            'linhas' => [
                [
                    'rotulo' => 'Clientes',
                    'valor' => $clientes->count(),
                ],
                [
                    'rotulo' => 'Clientes com CEP',
                    'valor' => $clientes->pluck('cep')->filter()->count(),
                ],
                [
                    'rotulo' => 'Número do último cliente',
                    'valor' => $clientes->max('id'),
                ],
                [
                    'rotulo' => 'Bairros distintos',
                    'valor' => $clientes->pluck('bairro')->filter()->unique()
                        ->count(),
                ],
                [
                    'rotulo' => 'Clientes do sexo masculino',
                    'valor' => $clientes->where('sexo', 'M')->count(),
                ],
                [
                    'rotulo' => 'Clientes com endereço',
                    'valor' => $clientes->pluck('endereco')->filter()->count(),
                ],
                [
                    'rotulo' => 'Estados distintos',
                    'valor' => $clientes->pluck('estado')->filter()->unique()
                        ->count(),
                ],
                [
                    'rotulo' => 'Primeiro cadastro em',
                    'valor' => $clientes->min('created_at'),
                    'formato' => 'data_hora',
                ],
                [
                    'rotulo' => 'Clientes do sexo feminino',
                    'valor' => $clientes->where('sexo', 'F')->count(),
                ],
                [
                    'rotulo' => 'Cidades distintas',
                    'valor' => $clientes->pluck('cidade')->filter()->unique()
                        ->count(),
                ],
                [
                    'rotulo' => 'Clientes sem sexo informado',
                    'valor' => $clientes->where('sexo', 'I')->count(),
                ],
                [
                    'rotulo' => 'Clientes com data de nascimento',
                    'valor' => $clientes->pluck('nascimento')->filter()
                        ->count(),
                ],
                [
                    'rotulo' => 'Último cadastro em',
                    'valor' => $clientes->max('created_at'),
                    'formato' => 'data_hora',
                ],
            ],
        ];
    }

    /**
     * Monta a seção "Vendas com desconto" do relatório.
     *
     * @param string $inicio Data inicial do período (Y-m-d).
     * @param string $fim    Data final do período (Y-m-d).
     *
     * @return array
     */
    private function secaoVendasComDesconto($inicio, $fim)
    {
        $transacoes = Transacoes::whereBetween('data', [$inicio, $fim])
            ->where('desconto', '>', 0)
            ->get();

        return [
            'titulo' => 'Vendas com desconto',
            'linhas' => [
                [
                    'rotulo' => 'Quantidade de vendas',
                    'valor' => $transacoes->count(),
                ],
                [
                    'rotulo' => 'Maior desconto concedido',
                    'valor' => $transacoes->max('desconto'),
                    'formato' => 'percentual',
                ],
                [
                    'rotulo' => 'Número da última venda',
                    'valor' => $transacoes->max('id'),
                ],
                [
                    'rotulo' => 'Parcelas por venda (média)',
                    'valor' => $transacoes->avg('parcelas'),
                    'formato' => 'decimal',
                ],
                [
                    'rotulo' => 'Ticket médio',
                    'valor' => $transacoes->avg('total'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Vendas no débito',
                    'valor' => $transacoes->where('pagamento', 'DE')->count(),
                ],
                [
                    'rotulo' => 'Vendas de até R$ 20,00',
                    'valor' => $transacoes->where('total', '<=', 20)->count(),
                ],
                [
                    'rotulo' => 'Último dia com venda',
                    'valor' => $transacoes->max('data'),
                    'formato' => 'data',
                ],
                [
                    'rotulo' => 'Primeira venda registrada em',
                    'valor' => $transacoes->min('created_at'),
                    'formato' => 'data_hora',
                ],
                [
                    'rotulo' => 'Dias com venda',
                    'valor' => $transacoes->pluck('data')->unique()->count(),
                ],
                [
                    'rotulo' => 'Faturamento das vendas acima de R$ 100,00',
                    'valor' => $transacoes->where('total', '>', 100)
                        ->sum('total'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Vendas parceladas',
                    'valor' => $transacoes->where('parcelas', '>', 1)->count(),
                ],
                [
                    'rotulo' => 'Vendas com desconto de 10% ou mais',
                    'valor' => $transacoes->where('desconto', '>=', 10)
                        ->count(),
                ],
                [
                    'rotulo' => 'Valor total vendido',
                    'valor' => $transacoes->sum('total'),
                    'formato' => 'moeda',
                ],
            ],
        ];
    }

    /**
     * Monta a seção "Vendas sem desconto" do relatório.
     *
     * @param string $inicio Data inicial do período (Y-m-d).
     * @param string $fim    Data final do período (Y-m-d).
     *
     * @return array
     */
    private function secaoVendasSemDesconto($inicio, $fim)
    {
        $transacoes = Transacoes::whereBetween('data', [$inicio, $fim])
            ->where('desconto', 0)
            ->get();

        return [
            'titulo' => 'Vendas sem desconto',
            'linhas' => [
                [
                    'rotulo' => 'Quantidade de vendas',
                    'valor' => $transacoes->count(),
                ],
                [
                    'rotulo' => 'Ticket médio',
                    'valor' => $transacoes->avg('total'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Vendas acima de R$ 100,00',
                    'valor' => $transacoes->where('total', '>', 100)->count(),
                ],
                [
                    'rotulo' => 'Última venda registrada em',
                    'valor' => $transacoes->max('created_at'),
                    'formato' => 'data_hora',
                ],
                [
                    'rotulo' => 'Vendas em dinheiro',
                    'valor' => $transacoes->where('pagamento', 'DI')->count(),
                ],
                [
                    'rotulo' => 'Vendas no crédito',
                    'valor' => $transacoes->where('pagamento', 'CR')->count(),
                ],
                [
                    'rotulo' => 'Primeira venda registrada em',
                    'valor' => $transacoes->min('created_at'),
                    'formato' => 'data_hora',
                ],
                [
                    'rotulo' => 'Clientes distintos',
                    'valor' => $transacoes->pluck('cliente')->unique()->count(),
                ],
                [
                    'rotulo' => 'Parcelas por venda (média)',
                    'valor' => $transacoes->avg('parcelas'),
                    'formato' => 'decimal',
                ],
                [
                    'rotulo' => 'Vendas no débito',
                    'valor' => $transacoes->where('pagamento', 'DE')->count(),
                ],
                [
                    'rotulo' => 'Maior venda',
                    'valor' => $transacoes->max('total'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Vendas à vista',
                    'valor' => $transacoes->where('parcelas', 1)->count(),
                ],
                [
                    'rotulo' => 'Maior parcelamento',
                    'valor' => $transacoes->max('parcelas'),
                ],
                [
                    'rotulo' => 'Faturamento das vendas acima de R$ 100,00',
                    'valor' => $transacoes->where('total', '>', 100)
                        ->sum('total'),
                    'formato' => 'moeda',
                ],
            ],
        ];
    }

    /**
     * Monta a seção "Vendas acima de R$ 100,00" do relatório.
     *
     * @param string $inicio Data inicial do período (Y-m-d).
     * @param string $fim    Data final do período (Y-m-d).
     *
     * @return array
     */
    private function secaoVendasAcimaDeCem($inicio, $fim)
    {
        $transacoes = Transacoes::whereBetween('data', [$inicio, $fim])
            ->where('total', '>', 100)
            ->get();

        return [
            'titulo' => 'Vendas acima de R$ 100,00',
            'linhas' => [
                [
                    'rotulo' => 'Quantidade de vendas',
                    'valor' => $transacoes->count(),
                ],
                [
                    'rotulo' => 'Clientes distintos',
                    'valor' => $transacoes->pluck('cliente')->unique()->count(),
                ],
                [
                    'rotulo' => 'Vendas em dinheiro',
                    'valor' => $transacoes->where('pagamento', 'DI')->count(),
                ],
                [
                    'rotulo' => 'Vendas no crédito',
                    'valor' => $transacoes->where('pagamento', 'CR')->count(),
                ],
                [
                    'rotulo' => 'Maior venda',
                    'valor' => $transacoes->max('total'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Parcelas por venda (média)',
                    'valor' => $transacoes->avg('parcelas'),
                    'formato' => 'decimal',
                ],
                [
                    'rotulo' => 'Desconto médio',
                    'valor' => $transacoes->avg('desconto'),
                    'formato' => 'percentual',
                ],
                [
                    'rotulo' => 'Vendas à vista',
                    'valor' => $transacoes->where('parcelas', 1)->count(),
                ],
                [
                    'rotulo' => 'Maior valor de parcela',
                    'valor' => $transacoes->max('valor_parcelas'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Dias com venda',
                    'valor' => $transacoes->pluck('data')->unique()->count(),
                ],
                [
                    'rotulo' => 'Primeira venda registrada em',
                    'valor' => $transacoes->min('created_at'),
                    'formato' => 'data_hora',
                ],
                [
                    'rotulo' => 'Vendas com desconto',
                    'valor' => $transacoes->where('desconto', '>', 0)->count(),
                ],
                [
                    'rotulo' => 'Valor médio da parcela',
                    'valor' => $transacoes->avg('valor_parcelas'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Maior parcelamento',
                    'valor' => $transacoes->max('parcelas'),
                ],
            ],
        ];
    }

    /**
     * Monta a seção "Vendas de até R$ 20,00" do relatório.
     *
     * @param string $inicio Data inicial do período (Y-m-d).
     * @param string $fim    Data final do período (Y-m-d).
     *
     * @return array
     */
    private function secaoVendasDeAteVinte($inicio, $fim)
    {
        $transacoes = Transacoes::whereBetween('data', [$inicio, $fim])
            ->where('total', '<=', 20)
            ->get();

        return [
            'titulo' => 'Vendas de até R$ 20,00',
            'linhas' => [
                [
                    'rotulo' => 'Quantidade de vendas',
                    'valor' => $transacoes->count(),
                ],
                [
                    'rotulo' => 'Desconto médio',
                    'valor' => $transacoes->avg('desconto'),
                    'formato' => 'percentual',
                ],
                [
                    'rotulo' => 'Vendas no crédito',
                    'valor' => $transacoes->where('pagamento', 'CR')->count(),
                ],
                [
                    'rotulo' => 'Clientes distintos',
                    'valor' => $transacoes->pluck('cliente')->unique()->count(),
                ],
                [
                    'rotulo' => 'Última venda registrada em',
                    'valor' => $transacoes->max('created_at'),
                    'formato' => 'data_hora',
                ],
                [
                    'rotulo' => 'Menor venda',
                    'valor' => $transacoes->min('total'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Valor mediano das vendas',
                    'valor' => $transacoes->median('total'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Valor total vendido',
                    'valor' => $transacoes->sum('total'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Maior valor de parcela',
                    'valor' => $transacoes->max('valor_parcelas'),
                    'formato' => 'moeda',
                ],
                [
                    'rotulo' => 'Vendas com desconto',
                    'valor' => $transacoes->where('desconto', '>', 0)->count(),
                ],
                [
                    'rotulo' => 'Número da última venda',
                    'valor' => $transacoes->max('id'),
                ],
                [
                    'rotulo' => 'Vendas no débito',
                    'valor' => $transacoes->where('pagamento', 'DE')->count(),
                ],
                [
                    'rotulo' => 'Primeira venda registrada em',
                    'valor' => $transacoes->min('created_at'),
                    'formato' => 'data_hora',
                ],
                [
                    'rotulo' => 'Valor médio da parcela',
                    'valor' => $transacoes->avg('valor_parcelas'),
                    'formato' => 'moeda',
                ],
            ],
        ];
    }
}
