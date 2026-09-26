<?php

namespace App\Experimento\C23\V2;

/**
 * Pagamento: cartão de crédito internacional.
 */
class CartaoDeCreditoInternacional extends FormaDePagamento
{
    /**
     * Código gravado na coluna pagamento das transações.
     *
     * @return string
     */
    public function codigo()
    {
        return 'CI';
    }

    /**
     * Nome da forma de pagamento, para exibição.
     *
     * @return string
     */
    public function nome()
    {
        return 'Cartão de crédito internacional';
    }

    /**
     * Taxa cobrada pela operadora, em % do valor.
     *
     * @return float
     */
    public function taxa()
    {
        return 4.99;
    }
}
