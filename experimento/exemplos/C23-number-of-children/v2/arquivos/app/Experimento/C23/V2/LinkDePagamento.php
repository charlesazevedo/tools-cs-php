<?php

namespace App\Experimento\C23\V2;

/**
 * Pagamento: link de pagamento.
 */
class LinkDePagamento extends FormaDePagamento
{
    /**
     * Código gravado na coluna pagamento das transações.
     *
     * @return string
     */
    public function codigo()
    {
        return 'LP';
    }

    /**
     * Nome da forma de pagamento, para exibição.
     *
     * @return string
     */
    public function nome()
    {
        return 'Link de pagamento';
    }

    /**
     * Taxa cobrada pela operadora, em % do valor.
     *
     * @return float
     */
    public function taxa()
    {
        return 3.49;
    }
}
