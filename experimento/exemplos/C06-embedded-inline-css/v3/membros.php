<?php

/**
 * Fragmento da variante C06/v3: atributo style gerado numa string PHP por um
 * método do controlador.
 */
class Fragmento
{
    /**
     * Monta o selo que mostra o saldo do caixa, em vermelho quando negativo.
     *
     * @param float $saldo saldo atual do caixa
     * @return string
     */
    public function montarSeloDeSaldo($saldo)
    {
        $cor = $saldo < 0 ? '#dd4b39' : '#00a65a';
        $valor = number_format($saldo, 2, ',', '.');

        return '<span class="label" style="background-color: ' . $cor . '; font-size: 14px;">R$ '
            . $valor . '</span>';
    }
}
