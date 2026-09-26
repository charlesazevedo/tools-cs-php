<?php

/**
 * Fragmento da variante C33/v2: método documentado com comentários de linha
 * (//) em vez de /**.
 */
class Fragmento
{
    // Calcula o total de um item da venda a partir do preço unitário e da
    // quantidade vendida, arredondado em centavos.
    public function calcularTotalDoItem(float $preco, int $quantidade): float
    {
        return round($preco * $quantidade, 2);
    }
}
