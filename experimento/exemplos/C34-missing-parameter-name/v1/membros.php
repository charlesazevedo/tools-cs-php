<?php

/**
 * Fragmento da variante C34/v1: tag @param só com o tipo, sem o nome do
 * parâmetro (e sem descrição).
 */
class Fragmento
{
    /**
     * Calcula o saldo do caixa depois de uma sangria.
     *
     * @param float $saldo Saldo atual do caixa.
     * @param float
     *
     * @return float
     */
    public function descontarSangria(float $saldo, float $retirada): float
    {
        return round($saldo - $retirada, 2);
    }
}
