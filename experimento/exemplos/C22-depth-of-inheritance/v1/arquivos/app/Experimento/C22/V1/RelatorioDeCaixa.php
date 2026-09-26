<?php

namespace App\Experimento\C22\V1;

use App\Models\Caixa;

/**
 * Relatório que acrescenta o saldo inicial do caixa do dia.
 */
abstract class RelatorioDeCaixa extends RelatorioDeVendasEmDinheiro
{
    /**
     * Acrescenta a sua seção às seções herdadas.
     *
     * @return array
     */
    protected function secoes()
    {
        $secoes = parent::secoes();
        $secoes['saldo_inicial'] = Caixa::where('data', $this->data)
            ->value('inicial');

        return $secoes;
    }
}
