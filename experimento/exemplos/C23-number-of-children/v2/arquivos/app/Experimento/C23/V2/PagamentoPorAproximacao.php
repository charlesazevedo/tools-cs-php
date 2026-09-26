<?php

namespace App\Experimento\C23\V2;

/**
 * Pagamento: pagamento por aproximação.
 */
class PagamentoPorAproximacao extends FormaDePagamento
{
    /**
     * Código gravado na coluna pagamento das transações.
     *
     * @return string
     */
    public function codigo()
    {
        return 'AP';
    }

    /**
     * Nome da forma de pagamento, para exibição.
     *
     * @return string
     */
    public function nome()
    {
        return 'Pagamento por aproximação';
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
