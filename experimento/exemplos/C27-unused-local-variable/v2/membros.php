<?php

/**
 * Fragmento da variante C27/v2: variável de valor do foreach nunca usada.
 */
class Fragmento
{
    /**
     * Conta as vendas registradas no dia.
     *
     * @return int
     */
    public function quantidadeDeVendasDoDia()
    {
        $quantidade = 0;
        foreach (Transacoes::today() as $transacao) {
            $quantidade++;
        }
        return $quantidade;
    }
}
