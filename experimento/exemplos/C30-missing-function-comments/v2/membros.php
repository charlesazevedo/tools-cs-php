<?php

/**
 * Fragmento da variante C30/v2: método privado sem comentário de documentação,
 * chamado por um método público documentado.
 */
class Fragmento
{
    /**
     * Formata, em reais, o saldo do caixa depois de uma sangria.
     *
     * @param float $saldo    Saldo atual do caixa.
     * @param float $retirada Valor retirado na sangria.
     *
     * @return string
     */
    public function formatarSaldo(float $saldo, float $retirada): string
    {
        return $this->formatarEmReais($saldo - $retirada);
    }

    private function formatarEmReais(float $valor): string
    {
        return number_format($valor, 2, ',', '.');
    }
}
