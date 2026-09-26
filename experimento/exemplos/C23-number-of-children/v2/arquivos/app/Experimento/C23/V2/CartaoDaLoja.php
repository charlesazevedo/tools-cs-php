<?php

namespace App\Experimento\C23\V2;

/**
 * Pagamento: cartão da loja.
 */
class CartaoDaLoja extends FormaDePagamento
{
    /**
     * Código gravado na coluna pagamento das transações.
     *
     * @return string
     */
    public function codigo()
    {
        return 'CJ';
    }

    /**
     * Nome da forma de pagamento, para exibição.
     *
     * @return string
     */
    public function nome()
    {
        return 'Cartão da loja';
    }

    /**
     * Taxa cobrada pela operadora, em % do valor.
     *
     * @return float
     */
    public function taxa()
    {
        return 1.0;
    }
}
