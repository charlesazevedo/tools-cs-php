<?php

/**
 * Fragmento da variante C24/v3: o bloco que calcula o saldo esperado é idêntico
 * ao de App\Experimento\C24\V3\ConferenciaDeFechamento::compararComContado().
 */
class Fragmento
{
    /**
     * Informa o saldo que deveria haver em dinheiro no caixa do dia.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function saldoEsperadoDoDia()
    {
        $caixa = Caixa::today();
        $entradas = Entrada_caixa::today()->sum('valor');
        $sangrias = Sangria::today()->sum('valor');
        $vendasDinheiro = Transacoes::where('data', '=', date('Y-m-d'))
            ->where('pagamento', '=', 'DI')
            ->sum('total');
        $saldoEsperado = $caixa->inicial + $entradas + $vendasDinheiro - $sangrias;
        $resumo = [
            'inicial' => number_format($caixa->inicial, 2, ',', '.'),
            'entradas' => number_format($entradas, 2, ',', '.'),
            'sangrias' => number_format($sangrias, 2, ',', '.'),
            'vendas' => number_format($vendasDinheiro, 2, ',', '.'),
            'esperado' => number_format($saldoEsperado, 2, ',', '.'),
        ];
        return response()->json($resumo);
    }
}
