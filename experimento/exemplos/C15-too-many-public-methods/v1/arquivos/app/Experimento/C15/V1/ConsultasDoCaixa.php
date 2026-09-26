<?php

namespace App\Experimento\C15\V1;

use App\Models\Transacoes;

/**
 * Consultas rápidas sobre as vendas e o caixa do dia.
 */
class ConsultasDoCaixa
{
    /**
     * Soma das vendas do dia pagas em dinheiro.
     *
     * @return float
     */
    public function totalEmDinheiro()
    {
        return Transacoes::today()->where('pagamento', 'DI')->sum('total');
    }

    /**
     * Soma das vendas do dia no cartão de crédito.
     *
     * @return float
     */
    public function totalNoCredito()
    {
        return Transacoes::totalCreditoDay();
    }

    /**
     * Soma das vendas do dia no cartão de débito.
     *
     * @return float
     */
    public function totalNoDebito()
    {
        return Transacoes::totalDebitoDay();
    }

    /**
     * Soma de todas as vendas do dia.
     *
     * @return float
     */
    public function totalVendido()
    {
        return Transacoes::today()->sum('total');
    }

    /**
     * Número de vendas do dia.
     *
     * @return int
     */
    public function quantidadeDeVendas()
    {
        return Transacoes::today()->count();
    }

    /**
     * Valor médio das vendas do dia.
     *
     * @return float
     */
    public function ticketMedio()
    {
        return Transacoes::today()->avg('total');
    }

    /**
     * Valor da maior venda do dia.
     *
     * @return float
     */
    public function maiorVenda()
    {
        return Transacoes::today()->max('total');
    }

    /**
     * Valor da menor venda do dia.
     *
     * @return float
     */
    public function menorVenda()
    {
        return Transacoes::today()->min('total');
    }

    /**
     * Desconto médio concedido nas vendas do dia, em %.
     *
     * @return float
     */
    public function descontoMedio()
    {
        return Transacoes::today()->avg('desconto');
    }

    /**
     * Maior desconto concedido no dia, em %.
     *
     * @return int
     */
    public function descontoMaximo()
    {
        return Transacoes::today()->max('desconto');
    }

    /**
     * Número de vendas do dia em mais de uma parcela.
     *
     * @return int
     */
    public function vendasParceladas()
    {
        return Transacoes::today()->where('parcelas', '>', 1)->count();
    }
}
