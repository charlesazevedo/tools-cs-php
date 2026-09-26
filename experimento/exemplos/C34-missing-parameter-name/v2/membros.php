<?php

/**
 * Fragmento da variante C34/v2: tag @param com tipo e descrição, sem o nome do
 * parâmetro.
 */
class Fragmento
{
    /**
     * Calcula o saldo do caixa depois de uma sangria.
     *
     * @param float $saldo Saldo atual do caixa.
     * @param float Valor retirado na sangria.
     *
     * @return float
     */
    public function descontarSangria(float $saldo, float $retirada): float
    {
        return round($saldo - $retirada, 2);
    }
}
