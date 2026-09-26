<?php

namespace App\Experimento\C35\V2;

/**
 * Cálculos do fechamento do caixa.
 */
class FechamentoDeCaixa {
    /**
     * Calcula o saldo final do caixa a partir das entradas e das saídas do dia.
     *
     * @param float $entradas Total de entradas do dia, com o valor inicial.
     * @param float $saidas   Total de sangrias do dia.
     *
     * @return float
     */
    public function calcularSaldoFinal(float $entradas, float $saidas): float
    {
        return round($entradas - $saidas, 2);
    }
}
