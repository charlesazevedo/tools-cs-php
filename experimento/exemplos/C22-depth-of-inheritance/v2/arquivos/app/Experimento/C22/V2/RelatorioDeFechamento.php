<?php

namespace App\Experimento\C22\V2;

use App\Models\Caixa;

/**
 * Relatório de fechamento do caixa do dia.
 */
class RelatorioDeFechamento extends RelatorioDeCaixaComEstoque
{
    /**
     * Título do relatório.
     *
     * @return string
     */
    protected function titulo()
    {
        return 'Fechamento do caixa';
    }

    /**
     * Acrescenta a sua seção às seções herdadas.
     *
     * @return array
     */
    protected function secoes()
    {
        $secoes = parent::secoes();
        $secoes['saldo_final'] = Caixa::where('data', $this->data)
            ->value('valor');

        return $secoes;
    }
}
