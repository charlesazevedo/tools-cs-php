<?php

namespace App\Experimento\C22\V2;

use App\Models\Estoque;

/**
 * Relatório de caixa que acrescenta as unidades em estoque.
 */
abstract class RelatorioDeCaixaComEstoque extends RelatorioDeCaixaComClientes
{
    /**
     * Acrescenta a sua seção às seções herdadas.
     *
     * @return array
     */
    protected function secoes()
    {
        $secoes = parent::secoes();
        $secoes['estoque'] = Estoque::Total();

        return $secoes;
    }
}
