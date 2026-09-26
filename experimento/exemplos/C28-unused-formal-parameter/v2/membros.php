<?php

/**
 * Fragmento da variante C28/v2: primeiro parâmetro de um método público nunca
 * usado (o segundo é usado).
 */
class Fragmento
{
    /**
     * Calcula o total de entradas de dinheiro no caixa, formatado.
     *
     * @param string $data          Data do caixa (AAAA-MM-DD).
     * @param int    $casasDecimais Número de casas decimais.
     *
     * @return string
     */
    public function totalDeEntradasFormatado($data, $casasDecimais)
    {
        return number_format(Entrada_caixa::today()->sum('valor'), $casasDecimais, ',', '.');
    }
}
