<?php

/**
 * Fragmento da variante C30/v1: método público sem comentário de documentação.
 */
class Fragmento
{
    public function aplicarDesconto(float $total, float $desconto): float
    {
        return round($total * (1 - ($desconto / 100)), 2);
    }
}
