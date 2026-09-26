<?php

/**
 * Fragmento da variante C37/v1: chamada de função em várias linhas com o
 * parêntese de fechamento na linha do último argumento.
 */
class Fragmento
{
    /**
     * Formata, em reais, o saldo do caixa a partir das entradas e saídas.
     *
     * @param float $entradas Total de entradas do dia, com o valor inicial.
     * @param float $saidas   Total de sangrias do dia.
     *
     * @return string
     */
    public function formatarSaldo(float $entradas, float $saidas): string
    {
        return number_format(
            $entradas - $saidas,
            2,
            ',',
            '.');
    }
}
