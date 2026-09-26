<?php

/**
 * Fragmento da variante C32/v3: grupo das tags @param depois de outro grupo.
 */
class Fragmento
{
    /**
     * Calcula o troco a devolver ao cliente no pagamento em dinheiro.
     *
     * @return float Troco devido ao cliente.
     *
     * @param float $valorPago Valor entregue pelo cliente.
     * @param float $total     Total da venda.
     */
    public function calcularTroco(float $valorPago, float $total): float
    {
        return round($valorPago - $total, 2);
    }
}
