<?php

/**
 * Fragmento da variante C24/v1: dois métodos com corpos idênticos (clone tipo 1).
 */
class Fragmento
{
    /**
     * Monta o resumo das transações do dia para o fechamento do caixa.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resumoParaFechamento()
    {
        $transacoes = Transacoes::today();
        $totalDinheiro = 0;
        $totalCredito = 0;
        $totalDebito = 0;
        foreach ($transacoes as $transacao) {
            $totalDinheiro += $transacao->pagamento == 'DI' ? $transacao->total : 0;
            $totalCredito += $transacao->pagamento == 'CR' ? $transacao->total : 0;
            $totalDebito += $transacao->pagamento == 'DE' ? $transacao->total : 0;
        }
        $totalGeral = $totalDinheiro + $totalCredito + $totalDebito;
        return response()->json([
            'dinheiro' => number_format($totalDinheiro, 2, ',', '.'),
            'credito' => number_format($totalCredito, 2, ',', '.'),
            'debito' => number_format($totalDebito, 2, ',', '.'),
            'total' => number_format($totalGeral, 2, ',', '.'),
            'quantidade' => count($transacoes),
        ]);
    }

    /**
     * Monta o resumo das transações do dia para a conferência do gerente.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resumoParaConferencia()
    {
        $transacoes = Transacoes::today();
        $totalDinheiro = 0;
        $totalCredito = 0;
        $totalDebito = 0;
        foreach ($transacoes as $transacao) {
            $totalDinheiro += $transacao->pagamento == 'DI' ? $transacao->total : 0;
            $totalCredito += $transacao->pagamento == 'CR' ? $transacao->total : 0;
            $totalDebito += $transacao->pagamento == 'DE' ? $transacao->total : 0;
        }
        $totalGeral = $totalDinheiro + $totalCredito + $totalDebito;
        return response()->json([
            'dinheiro' => number_format($totalDinheiro, 2, ',', '.'),
            'credito' => number_format($totalCredito, 2, ',', '.'),
            'debito' => number_format($totalDebito, 2, ',', '.'),
            'total' => number_format($totalGeral, 2, ',', '.'),
            'quantidade' => count($transacoes),
        ]);
    }
}
