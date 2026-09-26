<?php

/**
 * Fragmento da variante C12/v1: método de 101 linhas (painelDetalhadoDoDia).
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
        $caixaSaldoInicial = Caixa::where('data', date('Y-m-d'))->value('inicial');
        $caixaSaldoAtual = Caixa::where('data', date('Y-m-d'))->value('valor');
        $itensSomaValores = $itens->sum('valor_venda');
        $itensValorMedio = $itens->avg('valor_venda');
        $itensValorMediano = $itens->median('valor_venda');
        $sangriasTotal = $sangrias->sum('valor');
        $sangriasMedia = $sangrias->avg('valor');
        $sangriasMediana = $sangrias->median('valor');
        $sangriasMaior = $sangrias->max('valor');
        $entradasTotal = $entradas->sum('valor');

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
            ],
            'entradas' => [
                'quantidade' => $entradas->count(),
                'total' => [
                    'valor' => $entradasTotal,
                    'formatado' => number_format($entradasTotal, 2, ',', '.'),
                ],
            ],
            'estoque' => [
                'quantidade' => $produtos->count(),
            ],
        ]);
    }
}
