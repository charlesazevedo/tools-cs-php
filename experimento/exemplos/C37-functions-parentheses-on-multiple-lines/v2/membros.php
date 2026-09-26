<?php

/**
 * Fragmento da variante C37/v2: chamada de função em várias linhas com o
 * primeiro argumento na linha do parêntese de abertura.
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
        return number_format($entradas - $saidas,
            2,
            ',',
            '.'
        );
    }
}
