<?php

namespace App\Experimento\C24\V2;

use App\Models\Sangria;

/**
 * Relatório das sangrias (retiradas) feitas no caixa do dia.
 */
class RelatorioDeSangrias
{
    /**
     * Lista as sangrias do dia, com o total retirado.
     *
     * @return array
     */
    public function linhasDasSangrias()
    {
        $sangrias = Sangria::today();
        $linhas = [];
        $total = 0;
        foreach ($sangrias as $sangria) {
            $total += $sangria->valor;
            $linhas[] = [
                'descricao' => $sangria->descricao,
                'valor' => number_format($sangria->valor, 2, ',', '.'),
                'hora' => $sangria->created_at->format('H:i'),
            ];
        }
        return [
            'titulo' => 'Sangrias do dia',
            'itens' => $linhas,
            'quantidade' => count($linhas),
            'total' => number_format($total, 2, ',', '.'),
        ];
    }
}
