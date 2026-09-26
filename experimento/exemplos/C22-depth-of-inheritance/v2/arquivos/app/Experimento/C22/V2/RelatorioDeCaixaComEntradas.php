<?php

namespace App\Experimento\C22\V2;

use App\Models\Entrada_caixa;

/**
 * Relatório de caixa que acrescenta as entradas do dia.
 */
abstract class RelatorioDeCaixaComEntradas extends RelatorioDeCaixaComSangrias
{
    /**
     * Acrescenta a sua seção às seções herdadas.
     *
     * @return array
     */
    protected function secoes()
    {
        $secoes = parent::secoes();
        $secoes['entradas'] = Entrada_caixa::query()
            ->whereDate('created_at', $this->data)
            ->sum('valor');

        return $secoes;
    }
}
