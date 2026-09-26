<?php

namespace App\Experimento\C17\V1;

use App\Models\Caixa;
use App\Models\Entrada_caixa;
use App\Models\Sangria;
use App\Models\Transacoes;

/**
 * Painel do caixa com indicadores públicos, carregados sob demanda, por grupo.
 */
class PainelPublicoDoCaixa
{
    /**
     * Soma de todas as vendas do dia.
     *
     * @var float
     */
    public $totalVendido;

    /**
     * Número de vendas do dia.
     *
     * @var int
     */
    public $quantidadeDeVendas;

    /**
     * Valor médio das vendas do dia.
     *
     * @var float
     */
    public $ticketMedio;

    /**
     * Valor da maior venda do dia.
     *
     * @var float
     */
    public $maiorVenda;

    /**
     * Valor da menor venda do dia.
     *
     * @var float
     */
    public $menorVenda;

    /**
     * Número de clientes distintos atendidos no dia.
     *
     * @var int
     */
    public $clientesAtendidos;

    /**
     * Soma das vendas do dia pagas em dinheiro.
     *
     * @var float
     */
    public $totalEmDinheiro;

    /**
     * Soma das vendas do dia no cartão de crédito.
     *
     * @var float
     */
    public $totalNoCredito;

    /**
     * Soma das vendas do dia no cartão de débito.
     *
     * @var float
     */
    public $totalNoDebito;

    /**
     * Número de vendas do dia no cartão de crédito.
     *
     * @var int
     */
    public $quantidadeNoCredito;

    /**
     * Número de vendas do dia no cartão de débito.
     *
     * @var int
     */
    public $quantidadeNoDebito;

    /**
     * Número de vendas do dia pagas em dinheiro.
     *
     * @var int
     */
    public $quantidadeEmDinheiro;

    /**
     * Desconto médio concedido nas vendas do dia, em %.
     *
     * @var float
     */
    public $descontoMedio;

    /**
     * Maior desconto concedido no dia, em %.
     *
     * @var int
     */
    public $descontoMaximo;

    /**
     * Número de vendas do dia em mais de uma parcela.
     *
     * @var int
     */
    public $vendasParceladas;

    /**
     * Soma das vendas do dia em mais de uma parcela.
     *
     * @var float
     */
    public $totalParcelado;

    /**
     * Número de vendas do dia em parcela única.
     *
     * @var int
     */
    public $vendasAVista;

    /**
     * Número médio de parcelas das vendas do dia.
     *
     * @var float
     */
    public $mediaDeParcelas;

    /**
     * Saldo com que o caixa do dia foi aberto.
     *
     * @var float
     */
    public $saldoInicial;

    /**
     * Saldo atual do caixa do dia.
     *
     * @var float
     */
    public $saldoAtual;

    /**
     * Indica se o caixa do dia está aberto.
     *
     * @var bool
     */
    public $caixaAberto;

    /**
     * Soma das sangrias do dia.
     *
     * @var float
     */
    public $totalDeSangrias;

    /**
     * Número de sangrias do dia.
     *
     * @var int
     */
    public $quantidadeDeSangrias;

    /**
     * Valor da maior sangria do dia.
     *
     * @var float
     */
    public $maiorSangria;

    /**
     * Soma das entradas de caixa do dia.
     *
     * @var float
     */
    public $totalDeEntradas;

    /**
     * Número de entradas de caixa do dia.
     *
     * @var int
     */
    public $quantidadeDeEntradas;

    /**
     * Valor da maior entrada de caixa do dia.
     *
     * @var float
     */
    public $maiorEntrada;

    /**
     * Soma das vendas do mês corrente.
     *
     * @var float
     */
    public $vendasDoMes;

    /**
     * Número de vendas do mês corrente.
     *
     * @var int
     */
    public $quantidadeDeVendasDoMes;

    /**
     * Soma das sangrias do mês corrente.
     *
     * @var float
     */
    public $sangriasDoMes;

    /**
     * Soma das entradas de caixa do mês corrente.
     *
     * @var float
     */
    public $entradasDoMes;

    /**
     * Soma das vendas do mês no cartão de crédito.
     *
     * @var float
     */
    public $creditoDoMes;

    /**
     * Soma das vendas do mês no cartão de débito.
     *
     * @var float
     */
    public $debitoDoMes;

    /**
     * Soma das vendas do mês pagas em dinheiro.
     *
     * @var float
     */
    public $dinheiroDoMes;

    /**
     * Soma das vendas do ano corrente.
     *
     * @var float
     */
    public $vendasDoAno;

    /**
     * Faturamento do ano anterior.
     *
     * @var float
     */
    public $faturamentoAnterior;

    /**
     * Carrega os indicadores de vendas do dia.
     *
     * @return $this
     */
    public function carregarVendas()
    {
        $this->totalVendido = Transacoes::today()->sum('total');
        $this->quantidadeDeVendas = Transacoes::today()->count();
        $this->ticketMedio = Transacoes::today()->avg('total');
        $this->maiorVenda = Transacoes::today()->max('total');
        $this->menorVenda = Transacoes::today()->min('total');
        $this->clientesAtendidos = Transacoes::today()->pluck('cliente')
            ->unique()
            ->count();

        return $this;
    }

    /**
     * Carrega as vendas do dia por forma de pagamento.
     *
     * @return $this
     */
    public function carregarPagamentos()
    {
        $this->totalEmDinheiro = Transacoes::today()->where('pagamento', 'DI')
            ->sum('total');
        $this->totalNoCredito = Transacoes::totalCreditoDay();
        $this->totalNoDebito = Transacoes::totalDebitoDay();
        $this->quantidadeNoCredito = Transacoes::today()
            ->where('pagamento', 'CR')
            ->count();
        $this->quantidadeNoDebito = Transacoes::today()
            ->where('pagamento', 'DE')
            ->count();
        $this->quantidadeEmDinheiro = Transacoes::today()
            ->where('pagamento', 'DI')
            ->count();

        return $this;
    }

    /**
     * Carrega os descontos concedidos no dia.
     *
     * @return $this
     */
    public function carregarDescontos()
    {
        $this->descontoMedio = Transacoes::today()->avg('desconto');
        $this->descontoMaximo = Transacoes::today()->max('desconto');

        return $this;
    }

    /**
     * Carrega os parcelamentos do dia.
     *
     * @return $this
     */
    public function carregarParcelamentos()
    {
        $this->vendasParceladas = Transacoes::today()->where('parcelas', '>', 1)
            ->count();
        $this->totalParcelado = Transacoes::today()->where('parcelas', '>', 1)
            ->sum('total');
        $this->vendasAVista = Transacoes::today()->where('parcelas', 1)
            ->count();
        $this->mediaDeParcelas = Transacoes::today()->avg('parcelas');

        return $this;
    }

    /**
     * Carrega a situação do caixa do dia.
     *
     * @return $this
     */
    public function carregarCaixa()
    {
        $this->saldoInicial = Caixa::where('data', date('Y-m-d'))
            ->value('inicial');
        $this->saldoAtual = Caixa::where('data', date('Y-m-d'))->value('valor');
        $this->caixaAberto = Caixa::checkOpen();

        return $this;
    }

    /**
     * Carrega as sangrias do dia.
     *
     * @return $this
     */
    public function carregarSangrias()
    {
        $this->totalDeSangrias = Sangria::today()->sum('valor');
        $this->quantidadeDeSangrias = Sangria::today()->count();
        $this->maiorSangria = Sangria::today()->max('valor');

        return $this;
    }

    /**
     * Carrega as entradas de caixa do dia.
     *
     * @return $this
     */
    public function carregarEntradas()
    {
        $this->totalDeEntradas = Entrada_caixa::today()->sum('valor');
        $this->quantidadeDeEntradas = Entrada_caixa::today()->count();
        $this->maiorEntrada = Entrada_caixa::today()->max('valor');

        return $this;
    }

    /**
     * Carrega os indicadores do mês corrente.
     *
     * @return $this
     */
    public function carregarMes()
    {
        $this->vendasDoMes = Transacoes::month()->sum('total');
        $this->quantidadeDeVendasDoMes = Transacoes::month()->count();
        $this->sangriasDoMes = Sangria::whereMonth('data', date('m'))
            ->sum('valor');
        $this->entradasDoMes = Entrada_caixa::query()
            ->whereMonth('created_at', date('m'))
            ->sum('valor');

        return $this;
    }

    /**
     * Carrega as vendas do mês por forma de pagamento.
     *
     * @return $this
     */
    public function carregarPagamentosDoMes()
    {
        $this->creditoDoMes = Transacoes::month()->where('pagamento', 'CR')
            ->sum('total');
        $this->debitoDoMes = Transacoes::month()->where('pagamento', 'DE')
            ->sum('total');
        $this->dinheiroDoMes = Transacoes::month()->where('pagamento', 'DI')
            ->sum('total');

        return $this;
    }

    /**
     * Carrega os indicadores do ano corrente e do anterior.
     *
     * @return $this
     */
    public function carregarAno()
    {
        $this->vendasDoAno = Transacoes::year()->sum('total');
        $this->faturamentoAnterior = Transacoes::Faturamento(date('Y') - 1);

        return $this;
    }
}
