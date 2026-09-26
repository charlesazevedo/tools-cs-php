<?php

namespace App\Experimento\C22\V1;

use App\Models\Transacoes;

/**
 * Relatório que acrescenta as vendas do dia em dinheiro.
 */
abstract class RelatorioDeVendasEmDinheiro extends RelatorioDeVendas
{
    /**
     * Acrescenta a sua seção às seções herdadas.
     *
     * @return array
     */
    protected function secoes()
    {
        $secoes = parent::secoes();
        $secoes['dinheiro'] = Transacoes::where('data', $this->data)
            ->where('pagamento', 'DI')
            ->sum('total');

        return $secoes;
    }
}
