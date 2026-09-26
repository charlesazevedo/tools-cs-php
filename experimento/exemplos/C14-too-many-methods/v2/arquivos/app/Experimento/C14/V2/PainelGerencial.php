<?php

namespace App\Experimento\C14\V2;

use App\Models\Caixa;
use App\Models\Cliente;
use App\Models\Entrada_caixa;
use App\Models\Estoque;
use App\Models\Estoque\Estoque_aux;
use App\Models\Sangria;
use App\Models\Transacoes;
use App\Models\Venda;

/**
 * Painel gerencial com os indicadores de vendas e caixa do dia, do mês e do
 * ano, do estoque e dos clientes.
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
            'menor_venda' => $this->menorVenda(),
            'clientes_atendidos' => $this->clientesAtendidos(),
            'itens_vendidos_hoje' => $this->itensVendidosHoje(),
        ];
    }

    /**
     * Descontos concedidos no dia.
     *
     * @return array
     */
    public function resumoDosDescontos()
    {
        return [
            'desconto_medio' => $this->descontoMedio(),
            'desconto_maximo' => $this->descontoMaximo(),
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
            'quantidade_em_dinheiro' => $this->quantidadeEmDinheiro(),
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
            'media_de_parcelas' => $this->mediaDeParcelas(),
            'maior_parcela' => $this->maiorParcela(),
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
     * Resumo das vendas do mês corrente.
     *
     * @return array
     */
    public function resumoDoMes()
    {
        return [
            'vendas_do_mes' => $this->vendasDoMes(),
            'quantidade_de_vendas_do_mes' => $this->quantidadeDeVendasDoMes(),
            'credito_do_mes' => $this->creditoDoMes(),
            'debito_do_mes' => $this->debitoDoMes(),
            'dinheiro_do_mes' => $this->dinheiroDoMes(),
        ];
    }

    /**
     * Resumo do ano corrente e do anterior.
     *
     * @return array
     */
    public function resumoDoAno()
    {
        return [
            'vendas_do_ano' => $this->vendasDoAno(),
            'faturamento_anterior' => $this->faturamentoAnterior(),
            'sangrias_do_ano' => $this->sangriasDoAno(),
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
            'margem_media_de_lucro' => $this->margemMediaDeLucro(),
            'variacoes_disponiveis' => $this->variacoesDisponiveis(),
        ];
    }

    /**
     * Resumo do cadastro de clientes.
     *
     * @return array
     */
    public function resumoDosClientes()
    {
        return [
            'clientes_cadastrados' => $this->clientesCadastrados(),
            'clientes_novos_do_mes' => $this->clientesNovosDoMes(),
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
     * Valor da menor venda do dia.
     *
     * @return float
     */
    private function menorVenda()
    {
        return Transacoes::today()->min('total');
    }

    /**
     * Número de clientes distintos atendidos no dia.
     *
     * @return int
     */
    private function clientesAtendidos()
    {
        return Transacoes::today()->pluck('cliente')->unique()->count();
    }

    /**
     * Número de unidades vendidas no dia.
     *
     * @return int
     */
    private function itensVendidosHoje()
    {
        return Venda::query()
            ->whereIn('transacao', Transacoes::today()->pluck('id'))
            ->sum('quantidade');
    }

    /**
     * Desconto médio concedido nas vendas do dia, em %.
     *
     * @return float
     */
    private function descontoMedio()
    {
        return Transacoes::today()->avg('desconto');
    }

    /**
     * Maior desconto concedido no dia, em %.
     *
     * @return int
     */
    private function descontoMaximo()
    {
        return Transacoes::today()->max('desconto');
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
     * Número de vendas do dia pagas em dinheiro.
     *
     * @return int
     */
    private function quantidadeEmDinheiro()
    {
        return Transacoes::today()->where('pagamento', 'DI')->count();
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
     * Número médio de parcelas das vendas do dia.
     *
     * @return float
     */
    private function mediaDeParcelas()
    {
        return Transacoes::today()->avg('parcelas');
    }

    /**
     * Maior valor de parcela entre as vendas do dia.
     *
     * @return float
     */
    private function maiorParcela()
    {
        return Transacoes::today()->max('valor_parcelas');
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
     * Soma das vendas do mês corrente.
     *
     * @return float
     */
    private function vendasDoMes()
    {
        return Transacoes::month()->sum('total');
    }

    /**
     * Número de vendas do mês corrente.
     *
     * @return int
     */
    private function quantidadeDeVendasDoMes()
    {
        return Transacoes::month()->count();
    }

    /**
     * Soma das vendas do mês no cartão de crédito.
     *
     * @return float
     */
    private function creditoDoMes()
    {
        return Transacoes::month()->where('pagamento', 'CR')->sum('total');
    }

    /**
     * Soma das vendas do mês no cartão de débito.
     *
     * @return float
     */
    private function debitoDoMes()
    {
        return Transacoes::month()->where('pagamento', 'DE')->sum('total');
    }

    /**
     * Soma das vendas do mês pagas em dinheiro.
     *
     * @return float
     */
    private function dinheiroDoMes()
    {
        return Transacoes::month()->where('pagamento', 'DI')->sum('total');
    }

    /**
     * Soma das vendas do ano corrente.
     *
     * @return float
     */
    private function vendasDoAno()
    {
        return Transacoes::year()->sum('total');
    }

    /**
     * Faturamento do ano anterior.
     *
     * @return float
     */
    private function faturamentoAnterior()
    {
        return Transacoes::Faturamento(date('Y') - 1);
    }

    /**
     * Soma das sangrias do ano corrente.
     *
     * @return float
     */
    private function sangriasDoAno()
    {
        return Sangria::whereYear('data', date('Y'))->sum('valor');
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

    /**
     * Margem de lucro média dos produtos, em %.
     *
     * @return float
     */
    private function margemMediaDeLucro()
    {
        return Estoque::avg('lucro');
    }

    /**
     * Número de variações (cor e tamanho) com estoque.
     *
     * @return int
     */
    private function variacoesDisponiveis()
    {
        return Estoque_aux::where('estoque', '>', 0)->count();
    }

    /**
     * Número de clientes cadastrados.
     *
     * @return int
     */
    private function clientesCadastrados()
    {
        return Cliente::count();
    }

    /**
     * Número de clientes cadastrados no mês corrente.
     *
     * @return int
     */
    private function clientesNovosDoMes()
    {
        return Cliente::whereMonth('created_at', date('m'))->count();
    }
}
