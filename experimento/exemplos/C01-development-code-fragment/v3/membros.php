<?php

/**
 * Fragmento da variante C01/v3: debug_print_backtrace() esquecido num método.
 */
class Fragmento
{
    /**
     * Calcula o troco de uma venda paga em dinheiro.
     *
     * @param float $valorPago  valor entregue pelo cliente
     * @param float $totalVenda valor total da venda
     * @return float
     */
    public function calcularTrocoDaVenda($valorPago, $totalVenda)
    {
        debug_print_backtrace();

        return round($valorPago - $totalVenda, 2);
    }
}
