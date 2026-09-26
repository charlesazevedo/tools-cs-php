<?php

namespace App\Experimento\C23\V1;

/**
 * Pagamento: cartão de débito.
 */
class CartaoDeDebito extends FormaDePagamento
{
    /**
     * Código gravado na coluna pagamento das transações.
     *
     * @return string
     */
    public function codigo()
    {
        return 'DE';
    }

    /**
     * Nome da forma de pagamento, para exibição.
     *
     * @return string
     */
    public function nome()
    {
        return 'Cartão de débito';
    }

    /**
     * Taxa cobrada pela operadora, em % do valor.
     *
     * @return float
     */
    public function taxa()
    {
        return 1.49;
    }
}
