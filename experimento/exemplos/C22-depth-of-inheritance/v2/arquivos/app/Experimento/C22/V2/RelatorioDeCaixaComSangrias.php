<?php

namespace App\Experimento\C22\V2;

use App\Models\Sangria;

/**
 * Relatório de caixa que acrescenta as sangrias do dia.
 */
abstract class RelatorioDeCaixaComSangrias extends RelatorioDeCaixa
{
    /**
     * Acrescenta a sua seção às seções herdadas.
     *
     * @return array
     */
    protected function secoes()
    {
        $secoes = parent::secoes();
        $secoes['sangrias'] = Sangria::where('data', $this->data)->sum('valor');

        return $secoes;
    }
}
