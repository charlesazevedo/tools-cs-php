<?php

namespace App\Experimento\C24\V3;

use App\Models\Caixa;
use App\Models\Entrada_caixa;
use App\Models\Sangria;
use App\Models\Transacoes;

/**
 * Conferência do dinheiro contado no fechamento do caixa.
 */
class ConferenciaDeFechamento
{
    /**
     * Compara o dinheiro contado na gaveta com o saldo esperado do dia.
     *
     * @param float $contado Valor contado na gaveta, em reais.
     *
     * @return array
     */
    public function compararComContado($contado)
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
        $resumo['contado'] = number_format($contado, 2, ',', '.');
        $resumo['diferenca'] = number_format($contado - $saldoEsperado, 2, ',', '.');
        return $resumo;
    }
}
