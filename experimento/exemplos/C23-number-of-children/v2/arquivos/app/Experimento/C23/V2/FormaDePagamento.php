<?php

namespace App\Experimento\C23\V2;

/**
 * Forma de pagamento aceita no caixa.
 */
abstract class FormaDePagamento
{
    /**
     * Código gravado na coluna pagamento das transações.
     *
     * @return string
     */
    abstract public function codigo();

    /**
     * Nome da forma de pagamento, para exibição.
     *
     * @return string
     */
    abstract public function nome();

    /**
     * Taxa cobrada pela operadora, em % do valor.
     *
     * @return float
     */
    abstract public function taxa();

    /**
     * Valor que o caixa recebe depois de descontada a taxa.
     *
     * @param float $valor Valor da venda.
     *
     * @return float
     */
    public function valorLiquido($valor)
    {
        return round($valor * (1 - $this->taxa() / 100), 2);
    }
}
