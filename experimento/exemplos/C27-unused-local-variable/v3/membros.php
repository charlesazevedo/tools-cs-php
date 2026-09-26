<?php

/**
 * Fragmento da variante C27/v3: variável criada por desestruturação com list()
 * e nunca usada.
 */
class Fragmento
{
    /**
     * Soma o total das transações a partir da data inicial de um período.
     *
     * @param string $periodo Datas inicial e final (AAAA-MM-DD), separadas por vírgula.
     *
     * @return float
     */
    public function totalDoPeriodo($periodo)
    {
        list($inicio, $fim) = explode(',', $periodo);
        return Transacoes::where('data', '>=', $inicio)->sum('total');
    }
}
