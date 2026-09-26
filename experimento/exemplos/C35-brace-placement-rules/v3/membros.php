<?php

/**
 * Fragmento da variante C35/v3: chave de abertura de uma estrutura de controle
 * em linha própria.
 */
class Fragmento
{
    /**
     * Aplica ao total da venda um desconto percentual de no máximo 100%.
     *
     * @param float $total    Total da venda.
     * @param float $desconto Percentual de desconto.
     *
     * @return float
     */
    public function aplicarDesconto(float $total, float $desconto): float
    {
        $percentual = $desconto;
        if ($percentual > 100)
        {
            $percentual = 100;
        }

        return round($total * (1 - ($percentual / 100)), 2);
    }
}
