<?php

/**
 * Fragmento da variante C36/v3: elementos de um array literal sem espaço
 * depois da vírgula.
 */
class Fragmento
{
    /**
     * Informa se a forma de pagamento é uma das aceitas no caixa.
     *
     * @param string $pagamento Código da forma de pagamento.
     *
     * @return bool
     */
    public function aceitaPagamento(string $pagamento): bool
    {
        return in_array($pagamento, ['DI','CR','DE'], true);
    }
}
