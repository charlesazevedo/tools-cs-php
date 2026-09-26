<?php

namespace App\Experimento\C24\V2;

use App\Models\Entrada_caixa;

/**
 * Relatório das entradas de dinheiro feitas no caixa do dia.
 */
class RelatorioDeEntradas
{
    /**
     * Lista as entradas do dia, com o total adicionado.
     *
     * @return array
     */
    public function linhasDasEntradas()
    {
        $entradas = Entrada_caixa::today();
        $registros = [];
        $soma = 0;
        foreach ($entradas as $entrada) {
            $soma += $entrada->valor;
            $registros[] = [
                'descricao' => $entrada->descricao,
                'valor' => number_format($entrada->valor, 2, ',', '.'),
                'hora' => $entrada->created_at->format('H:i'),
            ];
        }
        return [
            'titulo' => 'Entradas do dia',
            'itens' => $registros,
            'quantidade' => count($registros),
            'total' => number_format($soma, 2, ',', '.'),
        ];
    }
}
