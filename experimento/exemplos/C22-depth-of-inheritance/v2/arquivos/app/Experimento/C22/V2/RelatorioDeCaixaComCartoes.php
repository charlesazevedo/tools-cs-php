<?php

namespace App\Experimento\C22\V2;

use App\Models\Transacoes;

/**
 * Relatório de caixa que acrescenta as vendas do dia no cartão (crédito e
 * débito).
 */
abstract class RelatorioDeCaixaComCartoes extends RelatorioDeCaixaComEntradas
{
    /**
     * Acrescenta a sua seção às seções herdadas.
     *
     * @return array
     */
    protected function secoes()
    {
        $secoes = parent::secoes();
        $secoes['cartoes'] = Transacoes::where('data', $this->data)
            ->whereIn('pagamento', ['CR', 'DE'])
            ->sum('total');

        return $secoes;
    }
}
