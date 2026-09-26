<?php

/**
 * Fragmento da variante C32/v2: tags @param divididas em dois grupos.
 */
class Fragmento
{
    /**
     * Calcula o troco a devolver ao cliente no pagamento em dinheiro.
     *
     * @param float $valorPago Valor entregue pelo cliente.
     *
     * @param float $total     Total da venda.
     *
     * @return float Troco devido ao cliente.
     */
    public function calcularTroco(float $valorPago, float $total): float
    {
        return round($valorPago - $total, 2);
    }
}
