<?php

namespace App\Experimento\C22\V2;

use App\Models\Transacoes;

/**
 * Relatório de caixa que acrescenta o desconto médio do dia.
 */
abstract class RelatorioDeCaixaComDescontos extends RelatorioDeCaixaComCartoes
{
    /**
     * Acrescenta a sua seção às seções herdadas.
     *
     * @return array
     */
    protected function secoes()
    {
        $secoes = parent::secoes();
        $secoes['desconto_medio'] = Transacoes::where('data', $this->data)
            ->avg('desconto');

        return $secoes;
    }
}
