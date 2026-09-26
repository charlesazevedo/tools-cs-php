<?php

/**
 * Fragmento da variante C27/v1: variável local atribuída e nunca lida.
 */
class Fragmento
{
    /**
     * Calcula o total retirado em sangrias no dia.
     *
     * @return float
     */
    public function totalDeSangriasDoDia()
    {
        $sangrias = Sangria::today();
        $quantidade = count($sangrias);
        return $sangrias->sum('valor');
    }
}
