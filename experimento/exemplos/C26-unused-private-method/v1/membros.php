<?php

/**
 * Fragmento da variante C26/v1: método privado de instância nunca chamado.
 */
class Fragmento
{
    /**
     * Formata um valor em reais no padrão brasileiro.
     *
     * @param float $valor Valor em reais.
     *
     * @return string
     */
    private function formatarEmReais($valor)
    {
        return 'R$ ' . number_format($valor, 2, ',', '.');
    }
}
