<?php

namespace App\Experimento\C22\V2;

use App\Models\Transacoes;
use App\Models\Venda;

/**
 * Relatório de caixa que acrescenta as unidades vendidas no dia.
 */
abstract class RelatorioDeCaixaComItens extends RelatorioDeCaixaComParcelamentos
{
    /**
     * Acrescenta a sua seção às seções herdadas.
     *
     * @return array
     */
    protected function secoes()
    {
        $secoes = parent::secoes();
        $vendas = Transacoes::where('data', $this->data)->pluck('id');
        $secoes['itens'] = Venda::whereIn('transacao', $vendas)
            ->sum('quantidade');

        return $secoes;
    }
}
