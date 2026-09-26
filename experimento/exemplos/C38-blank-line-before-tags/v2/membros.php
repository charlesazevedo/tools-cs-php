<?php

/**
 * Fragmento da variante C38/v2: duas linhas em branco entre a descrição e as
 * tags do comentário de documentação.
 */
class Fragmento
{
    /**
     * Calcula o saldo do caixa depois de uma sangria.
     *
     *
     * @param float $saldo    Saldo atual do caixa.
     * @param float $retirada Valor retirado na sangria.
     *
     * @return float
     */
    public function descontarSangria(float $saldo, float $retirada): float
    {
        return round($saldo - $retirada, 2);
    }
}
