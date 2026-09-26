<?php

namespace App\Experimento\C33\V3;

/*
 * Totais dos itens de uma venda.
 */
class TotalizadorDeVenda
{
    /**
     * Calcula o total de um item da venda.
     *
     * @param float $preco      Preço unitário do produto.
     * @param int   $quantidade Quantidade vendida.
     *
     * @return float
     */
    public function calcularTotalDoItem(float $preco, int $quantidade): float
    {
        return round($preco * $quantidade, 2);
    }
}
