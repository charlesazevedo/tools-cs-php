<?php

/**
 * Fragmento da variante C26/v2: método privado estático nunca chamado.
 */
class Fragmento
{
    /**
     * Converte um valor digitado no formulário (1.234,56) para o formato do
     * banco de dados (1234.56).
     *
     * @param string $valorDigitado Valor como digitado pelo operador.
     *
     * @return string
     */
    private static function converterValorDigitado($valorDigitado)
    {
        return str_replace(['.', ','], ['', '.'], $valorDigitado);
    }
}
