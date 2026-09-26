<?php

namespace App\Experimento\C28\V3;

use App\Models\Caixa;

/**
 * Conferência do saldo de um caixa por um operador.
 */
class ConferenciaDeCaixa
{
    /**
     * Caixa conferido.
     *
     * @var Caixa
     */
    private $caixa;

    /**
     * Cria a conferência de um caixa.
     *
     * @param Caixa  $caixa    Caixa conferido.
     * @param string $operador Nome do operador que faz a conferência.
     */
    public function __construct(Caixa $caixa, $operador)
    {
        $this->caixa = $caixa;
    }

    /**
     * Devolve o saldo atual do caixa conferido.
     *
     * @return float
     */
    public function saldoAtual()
    {
        return $this->caixa->valor;
    }
}
