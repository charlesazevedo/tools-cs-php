<?php

namespace App\Experimento\C22\V2;

use App\Models\Transacoes;

/**
 * Relatório de caixa que acrescenta as vendas parceladas do dia.
 */
abstract class RelatorioDeCaixaComParcelamentos extends RelatorioDeCaixaComDescontos
{
    /**
     * Acrescenta a sua seção às seções herdadas.
     *
     * @return array
     */
    protected function secoes()
    {
        $secoes = parent::secoes();
        $secoes['parceladas'] = Transacoes::where('data', $this->data)
            ->where('parcelas', '>', 1)
            ->count();

        return $secoes;
    }
}
