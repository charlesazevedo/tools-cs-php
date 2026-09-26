<?php

/**
 * Fragmento da variante C35/v1: chave de abertura do método na mesma linha da
 * declaração.
 */
class Fragmento
{
    /**
     * Calcula o troco a devolver ao cliente no pagamento em dinheiro.
     *
     * @param float $valorPago Valor entregue pelo cliente.
     * @param float $total     Total da venda.
     *
     * @return float
     */
    public function calcularTroco(float $valorPago, float $total): float {
        return round($valorPago - $total, 2);
    }
}
