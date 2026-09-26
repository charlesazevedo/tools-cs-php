<?php

namespace App\Experimento\C22\V2;

use App\Models\Transacoes;

/**
 * Relatório de caixa que acrescenta os clientes atendidos no dia.
 */
abstract class RelatorioDeCaixaComClientes extends RelatorioDeCaixaComItens
{
    /**
     * Acrescenta a sua seção às seções herdadas.
     *
     * @return array
     */
    protected function secoes()
    {
        $secoes = parent::secoes();
        $secoes['clientes'] = Transacoes::where('data', $this->data)->distinct()
            ->count('cliente');

        return $secoes;
    }
}
