<?php

/**
 * Fragmento da variante C33/v1: método documentado com comentário de bloco
 * comum (um só asterisco na abertura) em vez de /**.
 */
class Fragmento
{
    /*
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
