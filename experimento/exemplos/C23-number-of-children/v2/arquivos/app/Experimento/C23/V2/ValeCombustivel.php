<?php

namespace App\Experimento\C23\V2;

/**
 * Pagamento: vale-combustível.
 */
class ValeCombustivel extends FormaDePagamento
{
    /**
     * Código gravado na coluna pagamento das transações.
     *
     * @return string
     */
    public function codigo()
    {
        return 'VC';
    }

    /**
     * Nome da forma de pagamento, para exibição.
     *
     * @return string
     */
    public function nome()
    {
        return 'Vale-combustível';
    }

    /**
     * Taxa cobrada pela operadora, em % do valor.
     *
     * @return float
     */
    public function taxa()
    {
        return 4.0;
    }
}
