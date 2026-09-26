<?php

/**
 * Fragmento da variante C12/v2: método de 200 linhas (painelDetalhadoDoDia).
 */
class Fragmento
{
    /**
     * Monta o painel detalhado do caixa do dia (vendas, caixa, itens
     * vendidos, sangrias, entradas e estoque), com os valores em reais
     * também formatados para exibição.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function painelDetalhadoDoDia()
    {
        $transacoes = Transacoes::today();
        $itens = Venda::whereIn('transacao', $transacoes->pluck('id'))->get();
        $sangrias = Sangria::today();
        $entradas = Entrada_caixa::today();
        $produtos = Estoque::all();
        $vendasTotal = $transacoes->sum('total');
        $vendasTicketMedio = $transacoes->avg('total');
        $vendasMediana = $transacoes->median('total');
        $vendasMaiorVenda = $transacoes->max('total');
        $vendasMenorVenda = $transacoes->min('total');
        $caixaSaldoInicial = Caixa::where('data', date('Y-m-d'))->value('inicial');
        $caixaSaldoAtual = Caixa::where('data', date('Y-m-d'))->value('valor');
        $itensSomaValores = $itens->sum('valor_venda');
        $itensValorMedio = $itens->avg('valor_venda');
        $itensValorMediano = $itens->median('valor_venda');
        $itensMaiorValor = $itens->max('valor_venda');
        $itensMenorValor = $itens->min('valor_venda');
        $sangriasTotal = $sangrias->sum('valor');
        $sangriasMedia = $sangrias->avg('valor');
        $sangriasMediana = $sangrias->median('valor');
        $sangriasMaior = $sangrias->max('valor');
        $sangriasMenor = $sangrias->min('valor');
        $sangriasTotalAcimaDeCem = $sangrias->where('valor', '>', 100)->sum('valor');
        $entradasTotal = $entradas->sum('valor');
        $entradasMedia = $entradas->avg('valor');
        $entradasMediana = $entradas->median('valor');
        $entradasMaior = $entradas->max('valor');
        $entradasMenor = $entradas->min('valor');
        $entradasTotalAcimaDeCem = $entradas->where('valor', '>', 100)->sum('valor');
        $estoquePrecoMedio = $produtos->avg('preco');
        $estoquePrecoMediano = $produtos->median('preco');
        $estoqueMaiorPreco = $produtos->max('preco');
        $estoqueMenorPreco = $produtos->min('preco');
        $estoqueCustoMedio = $produtos->avg('preco_custo');

        return response()->json([
            'data' => date('d/m/Y'),
            'vendas' => [
                'quantidade' => $transacoes->count(),
                'total' => [
                    'valor' => $vendasTotal,
                    'formatado' => number_format($vendasTotal, 2, ',', '.'),
                ],
                'ticket_medio' => [
                    'valor' => $vendasTicketMedio,
                    'formatado' => number_format($vendasTicketMedio, 2, ',', '.'),
                ],
                'mediana' => [
                    'valor' => $vendasMediana,
                    'formatado' => number_format($vendasMediana, 2, ',', '.'),
                ],
                'maior_venda' => [
                    'valor' => $vendasMaiorVenda,
                    'formatado' => number_format($vendasMaiorVenda, 2, ',', '.'),
                ],
                'menor_venda' => [
                    'valor' => $vendasMenorVenda,
                    'formatado' => number_format($vendasMenorVenda, 2, ',', '.'),
                ],
                'desconto_medio' => $transacoes->avg('desconto'),
                'maior_desconto' => $transacoes->max('desconto'),
                'com_desconto' => $transacoes->where('desconto', '>', 0)->count(),
                'desconto_alto' => $transacoes->where('desconto', '>=', 10)->count(),
                'media_parcelas' => $transacoes->avg('parcelas'),
                'maior_parcelamento' => $transacoes->max('parcelas'),
            ],
            'caixa' => [
                'aberto' => Caixa::checkOpen(),
                'saldo_inicial' => [
                    'valor' => $caixaSaldoInicial,
                    'formatado' => number_format($caixaSaldoInicial, 2, ',', '.'),
                ],
                'saldo_atual' => [
                    'valor' => $caixaSaldoAtual,
                    'formatado' => number_format($caixaSaldoAtual, 2, ',', '.'),
                ],
            ],
            'itens' => [
                'linhas' => $itens->count(),
                'unidades' => $itens->sum('quantidade'),
                'soma_valores' => [
                    'valor' => $itensSomaValores,
                    'formatado' => number_format($itensSomaValores, 2, ',', '.'),
                ],
                'valor_medio' => [
                    'valor' => $itensValorMedio,
                    'formatado' => number_format($itensValorMedio, 2, ',', '.'),
                ],
                'valor_mediano' => [
                    'valor' => $itensValorMediano,
                    'formatado' => number_format($itensValorMediano, 2, ',', '.'),
                ],
                'maior_valor' => [
                    'valor' => $itensMaiorValor,
                    'formatado' => number_format($itensMaiorValor, 2, ',', '.'),
                ],
                'menor_valor' => [
                    'valor' => $itensMenorValor,
                    'formatado' => number_format($itensMenorValor, 2, ',', '.'),
                ],
                'media_unidades' => $itens->avg('quantidade'),
                'maior_quantidade' => $itens->max('quantidade'),
                'produtos' => $itens->pluck('codigo_estoque')->unique()->count(),
                'variacoes' => $itens->pluck('codigo_estoque_aux')->unique()->count(),
                'vendas' => $itens->pluck('transacao')->unique()->count(),
            ],
            'sangrias' => [
                'quantidade' => $sangrias->count(),
                'total' => [
                    'valor' => $sangriasTotal,
                    'formatado' => number_format($sangriasTotal, 2, ',', '.'),
                ],
                'media' => [
                    'valor' => $sangriasMedia,
                    'formatado' => number_format($sangriasMedia, 2, ',', '.'),
                ],
                'mediana' => [
                    'valor' => $sangriasMediana,
                    'formatado' => number_format($sangriasMediana, 2, ',', '.'),
                ],
                'maior' => [
                    'valor' => $sangriasMaior,
                    'formatado' => number_format($sangriasMaior, 2, ',', '.'),
                ],
                'menor' => [
                    'valor' => $sangriasMenor,
                    'formatado' => number_format($sangriasMenor, 2, ',', '.'),
                ],
                'dias' => $sangrias->pluck('data')->unique()->count(),
                'primeiro_dia' => $sangrias->min('data'),
                'ultimo_dia' => $sangrias->max('data'),
                'motivos' => $sangrias->pluck('descricao')->unique()->count(),
                'acima_de_cem' => $sangrias->where('valor', '>', 100)->count(),
                'total_acima_de_cem' => [
                    'valor' => $sangriasTotalAcimaDeCem,
                    'formatado' => number_format($sangriasTotalAcimaDeCem, 2, ',', '.'),
                ],
            ],
            'entradas' => [
                'quantidade' => $entradas->count(),
                'total' => [
                    'valor' => $entradasTotal,
                    'formatado' => number_format($entradasTotal, 2, ',', '.'),
                ],
                'media' => [
                    'valor' => $entradasMedia,
                    'formatado' => number_format($entradasMedia, 2, ',', '.'),
                ],
                'mediana' => [
                    'valor' => $entradasMediana,
                    'formatado' => number_format($entradasMediana, 2, ',', '.'),
                ],
                'maior' => [
                    'valor' => $entradasMaior,
                    'formatado' => number_format($entradasMaior, 2, ',', '.'),
                ],
                'menor' => [
                    'valor' => $entradasMenor,
                    'formatado' => number_format($entradasMenor, 2, ',', '.'),
                ],
                'motivos' => $entradas->pluck('descricao')->unique()->count(),
                'acima_de_cem' => $entradas->where('valor', '>', 100)->count(),
                'total_acima_de_cem' => [
                    'valor' => $entradasTotalAcimaDeCem,
                    'formatado' => number_format($entradasTotalAcimaDeCem, 2, ',', '.'),
                ],
                'ate_cinquenta' => $entradas->where('valor', '<=', 50)->count(),
            ],
            'estoque' => [
                'quantidade' => $produtos->count(),
                'unidades' => $produtos->sum('estoque'),
                'media_unidades' => $produtos->avg('estoque'),
                'maior_estoque' => $produtos->max('estoque'),
                'menor_estoque' => $produtos->min('estoque'),
                'estoque_baixo' => $produtos->where('estoque', '<', 5)->count(),
                'preco_medio' => [
                    'valor' => $estoquePrecoMedio,
                    'formatado' => number_format($estoquePrecoMedio, 2, ',', '.'),
                ],
                'preco_mediano' => [
                    'valor' => $estoquePrecoMediano,
                    'formatado' => number_format($estoquePrecoMediano, 2, ',', '.'),
                ],
                'maior_preco' => [
                    'valor' => $estoqueMaiorPreco,
                    'formatado' => number_format($estoqueMaiorPreco, 2, ',', '.'),
                ],
                'menor_preco' => [
                    'valor' => $estoqueMenorPreco,
                    'formatado' => number_format($estoqueMenorPreco, 2, ',', '.'),
                ],
                'custo_medio' => [
                    'valor' => $estoqueCustoMedio,
                    'formatado' => number_format($estoqueCustoMedio, 2, ',', '.'),
                ],
            ],
        ]);
    }
}
