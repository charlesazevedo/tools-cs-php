<?php

namespace App\Experimento\C22\V2;

use App\Models\Transacoes;

/**
 * Relatório que acrescenta o total vendido no dia.
 */
abstract class RelatorioDeVendas extends Relatorio
{
    /**
     * Acrescenta a sua seção às seções herdadas.
     *
     * @return array
     */
    protected function secoes()
    {
        $secoes = parent::secoes();
        $secoes['vendas'] = Transacoes::where('data', $this->data)
            ->sum('total');

        return $secoes;
    }
}
