<?php

/**
 * Fragmento da variante C10/v2: HTML gerado numa string PHP com uma tag sem fechamento.
 */
class Fragmento
{
    /**
     * Monta o alerta exibido quando o valor da sangria é maior que o saldo do caixa.
     *
     * @param float $saldo saldo atual do caixa
     * @return string
     */
    public function montarAlertaDeSaldoInsuficiente($saldo)
    {
        $valor = number_format($saldo, 2, ',', '.');

        return '<div class="alert alert-danger"><strong>Saldo insuficiente:</div>'
            . '<p>O caixa tem apenas R$ ' . $valor . ' disponíveis.</p>';
    }
}
