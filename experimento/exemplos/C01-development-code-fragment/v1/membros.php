<?php

/**
 * Fragmento da variante C01/v1: var_dump() esquecido num método.
 */
class Fragmento
{
    /**
     * Soma os valores das sangrias informadas e devolve o total retirado do caixa.
     *
     * @param array $sangrias lista de sangrias, cada uma com a chave 'valor'
     * @return float
     */
    public function somarSangriasDoDia(array $sangrias)
    {
        $total = 0.0;
        foreach ($sangrias as $sangria) {
            $total += (float) $sangria['valor'];
        }
        var_dump($total);

        return $total;
    }
}
