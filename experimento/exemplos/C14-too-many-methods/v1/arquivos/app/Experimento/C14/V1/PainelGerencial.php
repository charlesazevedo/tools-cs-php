<?php

namespace App\Experimento\C14\V1;

use App\Models\Caixa;
use App\Models\Entrada_caixa;
use App\Models\Estoque;
use App\Models\Sangria;
use App\Models\Transacoes;

/**
 * Painel gerencial com os indicadores de vendas, caixa e estoque do dia.
 */
class PainelGerencial
{
    /**
     * Resumo das vendas do dia.
     *
     * @return array
     */
    public function resumoDasVendas()
    {
        return [
            'total_vendido' => $this->totalVendido(),
            'quantidade_de_vendas' => $this->quantidadeDeVendas(),
            'ticket_medio' => $this->ticketMedio(),
            'maior_venda' => $this->maiorVenda(),
        ];
    }

    /**
     * Vendas do dia por forma de pagamento.
     *
     * @return array
     */
    public function resumoDosPagamentos()
    {
        return [
            'total_em_dinheiro' => $this->totalEmDinheiro(),
            'total_no_credito' => $this->totalNoCredito(),
            'total_no_debito' => $this->totalNoDebito(),
        ];
    }

    /**
     * Resumo dos parcelamentos do dia.
     *
     * @return array
     */
    public function resumoDosParcelamentos()
    {
        return [
            'vendas_parceladas' => $this->vendasParceladas(),
            'total_parcelado' => $this->totalParcelado(),
            'vendas_a_vista' => $this->vendasAVista(),
        ];
    }

    /**
     * Situação do caixa do dia.
     *
     * @return array
     */
    public function resumoDoCaixa()
    {
        return [
            'saldo_inicial' => $this->saldoInicial(),
            'saldo_atual' => $this->saldoAtual(),
            'caixa_aberto' => $this->caixaAberto(),
        ];
    }

    /**
     * Sangrias e entradas de caixa do dia.
     *
     * @return array
     */
    public function resumoDasMovimentacoes()
    {
        return [
            'total_de_sangrias' => $this->totalDeSangrias(),
            'quantidade_de_sangrias' => $this->quantidadeDeSangrias(),
            'total_de_entradas' => $this->totalDeEntradas(),
            'quantidade_de_entradas' => $this->quantidadeDeEntradas(),
        ];
    }

    /**
     * Situação do estoque.
     *
     * @return array
     */
    public function resumoDoEstoque()
    {
        return [
            'unidades_em_estoque' => $this->unidadesEmEstoque(),
            'valor_do_estoque' => $this->valorDoEstoque(),
            'produtos_sem_estoque' => $this->produtosSemEstoque(),
        ];
    }

    /**
     * Soma de todas as vendas do dia.
     *
     * @return float
     */
    private function totalVendido()
    {
        return Transacoes::today()->sum('total');
    }

    /**
     * Número de vendas do dia.
     *
     * @return int
     */
    private function quantidadeDeVendas()
    {
        return Transacoes::today()->count();
    }

    /**
     * Valor médio das vendas do dia.
     *
     * @return float
     */
    private function ticketMedio()
    {
        return Transacoes::today()->avg('total');
    }

    /**
     * Valor da maior venda do dia.
     *
     * @return float
     */
    private function maiorVenda()
    {
        return Transacoes::today()->max('total');
    }

    /**
     * Soma das vendas do dia pagas em dinheiro.
     *
     * @return float
     */
    private function totalEmDinheiro()
    {
        return Transacoes::today()->where('pagamento', 'DI')->sum('total');
    }

    /**
     * Soma das vendas do dia no cartão de crédito.
     *
     * @return float
     */
    private function totalNoCredito()
    {
        return Transacoes::totalCreditoDay();
    }

    /**
     * Soma das vendas do dia no cartão de débito.
     *
     * @return float
     */
    private function totalNoDebito()
    {
        return Transacoes::totalDebitoDay();
    }

    /**
     * Número de vendas do dia em mais de uma parcela.
     *
     * @return int
     */
    private function vendasParceladas()
    {
        return Transacoes::today()->where('parcelas', '>', 1)->count();
    }

    /**
     * Soma das vendas do dia em mais de uma parcela.
     *
     * @return float
     */
    private function totalParcelado()
    {
        return Transacoes::today()->where('parcelas', '>', 1)->sum('total');
    }

    /**
     * Número de vendas do dia em parcela única.
     *
     * @return int
     */
    private function vendasAVista()
    {
        return Transacoes::today()->where('parcelas', 1)->count();
    }

    /**
     * Saldo com que o caixa do dia foi aberto.
     *
     * @return float
     */
    private function saldoInicial()
    {
        return Caixa::where('data', date('Y-m-d'))->value('inicial');
    }

    /**
     * Saldo atual do caixa do dia.
     *
     * @return float
     */
    private function saldoAtual()
    {
        return Caixa::where('data', date('Y-m-d'))->value('valor');
    }

    /**
     * Indica se o caixa do dia está aberto.
     *
     * @return bool
     */
    private function caixaAberto()
    {
        return Caixa::checkOpen();
    }

    /**
     * Soma das sangrias do dia.
     *
     * @return float
     */
    private function totalDeSangrias()
    {
        return Sangria::today()->sum('valor');
    }

    /**
     * Número de sangrias do dia.
     *
     * @return int
     */
    private function quantidadeDeSangrias()
    {
        return Sangria::today()->count();
    }

    /**
     * Soma das entradas de caixa do dia.
     *
     * @return float
     */
    private function totalDeEntradas()
    {
        return Entrada_caixa::today()->sum('valor');
    }

    /**
     * Número de entradas de caixa do dia.
     *
     * @return int
     */
    private function quantidadeDeEntradas()
    {
        return Entrada_caixa::today()->count();
    }

    /**
     * Número de unidades em estoque, somadas as variações.
     *
     * @return int
     */
    private function unidadesEmEstoque()
    {
        return Estoque::Total();
    }

    /**
     * Valor do estoque a preço de venda, já formatado.
     *
     * @return string
     */
    private function valorDoEstoque()
    {
        return Estoque::valorTotalRS();
    }

    /**
     * Número de produtos sem unidades em estoque.
     *
     * @return int
     */
    private function produtosSemEstoque()
    {
        return Estoque::where('estoque', '<=', 0)->count();
    }
}
