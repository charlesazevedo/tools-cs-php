<?php

namespace App\Experimento\C16\V2;

use App\Models\Caixa;
use App\Models\Entrada_caixa;
use App\Models\Sangria;
use App\Models\Transacoes;

/**
 * Resumo do fechamento do caixa do dia.
 */
class ResumoDoFechamento
{
    /**
     * Soma das vendas do dia pagas em dinheiro.
     *
     * @var float
     */
    private $totalEmDinheiro;

    /**
     * Soma das vendas do dia no cartão de crédito.
     *
     * @var float
     */
    private $totalNoCredito;

    /**
     * Soma das vendas do dia no cartão de débito.
     *
     * @var float
     */
    private $totalNoDebito;

    /**
     * Soma de todas as vendas do dia.
     *
     * @var float
     */
    private $totalVendido;

    /**
     * Número de vendas do dia.
     *
     * @var int
     */
    private $quantidadeDeVendas;

    /**
     * Valor médio das vendas do dia.
     *
     * @var float
     */
    private $ticketMedio;

    /**
     * Valor da maior venda do dia.
     *
     * @var float
     */
    private $maiorVenda;

    /**
     * Valor da menor venda do dia.
     *
     * @var float
     */
    private $menorVenda;

    /**
     * Desconto médio concedido nas vendas do dia, em %.
     *
     * @var float
     */
    private $descontoMedio;

    /**
     * Maior desconto concedido no dia, em %.
     *
     * @var int
     */
    private $descontoMaximo;

    /**
     * Número de vendas do dia em mais de uma parcela.
     *
     * @var int
     */
    private $vendasParceladas;

    /**
     * Soma das vendas do dia em mais de uma parcela.
     *
     * @var float
     */
    private $totalParcelado;

    /**
     * Número de vendas do dia em parcela única.
     *
     * @var int
     */
    private $vendasAVista;

    /**
     * Número médio de parcelas das vendas do dia.
     *
     * @var float
     */
    private $mediaDeParcelas;

    /**
     * Número de clientes distintos atendidos no dia.
     *
     * @var int
     */
    private $clientesAtendidos;

    /**
     * Número de vendas do dia no cartão de crédito.
     *
     * @var int
     */
    private $quantidadeNoCredito;

    /**
     * Número de vendas do dia no cartão de débito.
     *
     * @var int
     */
    private $quantidadeNoDebito;

    /**
     * Número de vendas do dia pagas em dinheiro.
     *
     * @var int
     */
    private $quantidadeEmDinheiro;

    /**
     * Saldo com que o caixa do dia foi aberto.
     *
     * @var float
     */
    private $saldoInicial;

    /**
     * Saldo atual do caixa do dia.
     *
     * @var float
     */
    private $saldoAtual;

    /**
     * Indica se o caixa do dia está aberto.
     *
     * @var bool
     */
    private $caixaAberto;

    /**
     * Soma das sangrias do dia.
     *
     * @var float
     */
    private $totalDeSangrias;

    /**
     * Número de sangrias do dia.
     *
     * @var int
     */
    private $quantidadeDeSangrias;

    /**
     * Valor da maior sangria do dia.
     *
     * @var float
     */
    private $maiorSangria;

    /**
     * Soma das entradas de caixa do dia.
     *
     * @var float
     */
    private $totalDeEntradas;

    /**
     * Número de entradas de caixa do dia.
     *
     * @var int
     */
    private $quantidadeDeEntradas;

    /**
     * Valor da maior entrada de caixa do dia.
     *
     * @var float
     */
    private $maiorEntrada;

    /**
     * Soma das vendas do mês corrente.
     *
     * @var float
     */
    private $vendasDoMes;

    /**
     * Número de vendas do mês corrente.
     *
     * @var int
     */
    private $quantidadeDeVendasDoMes;

    /**
     * Soma das vendas do ano corrente.
     *
     * @var float
     */
    private $vendasDoAno;

    /**
     * Carrega os indicadores do fechamento do caixa do dia.
     */
    public function __construct()
    {
        $this->totalEmDinheiro = Transacoes::today()->where('pagamento', 'DI')
            ->sum('total');
        $this->totalNoCredito = Transacoes::totalCreditoDay();
        $this->totalNoDebito = Transacoes::totalDebitoDay();
        $this->totalVendido = Transacoes::today()->sum('total');
        $this->quantidadeDeVendas = Transacoes::today()->count();
        $this->ticketMedio = Transacoes::today()->avg('total');
        $this->maiorVenda = Transacoes::today()->max('total');
        $this->menorVenda = Transacoes::today()->min('total');
        $this->descontoMedio = Transacoes::today()->avg('desconto');
        $this->descontoMaximo = Transacoes::today()->max('desconto');
        $this->vendasParceladas = Transacoes::today()->where('parcelas', '>', 1)
            ->count();
        $this->totalParcelado = Transacoes::today()->where('parcelas', '>', 1)
            ->sum('total');
        $this->vendasAVista = Transacoes::today()->where('parcelas', 1)
            ->count();
        $this->mediaDeParcelas = Transacoes::today()->avg('parcelas');
        $this->clientesAtendidos = Transacoes::today()->pluck('cliente')
            ->unique()
            ->count();
        $this->quantidadeNoCredito = Transacoes::today()
            ->where('pagamento', 'CR')
            ->count();
        $this->quantidadeNoDebito = Transacoes::today()
            ->where('pagamento', 'DE')
            ->count();
        $this->quantidadeEmDinheiro = Transacoes::today()
            ->where('pagamento', 'DI')
            ->count();
        $this->saldoInicial = Caixa::where('data', date('Y-m-d'))
            ->value('inicial');
        $this->saldoAtual = Caixa::where('data', date('Y-m-d'))->value('valor');
        $this->caixaAberto = Caixa::checkOpen();
        $this->totalDeSangrias = Sangria::today()->sum('valor');
        $this->quantidadeDeSangrias = Sangria::today()->count();
        $this->maiorSangria = Sangria::today()->max('valor');
        $this->totalDeEntradas = Entrada_caixa::today()->sum('valor');
        $this->quantidadeDeEntradas = Entrada_caixa::today()->count();
        $this->maiorEntrada = Entrada_caixa::today()->max('valor');
        $this->vendasDoMes = Transacoes::month()->sum('total');
        $this->quantidadeDeVendasDoMes = Transacoes::month()->count();
        $this->vendasDoAno = Transacoes::year()->sum('total');
    }

    /**
     * Devolve os indicadores do fechamento, para a view ou para JSON.
     *
     * @return array
     */
    public function paraArray()
    {
        return [
            'total_em_dinheiro' => $this->totalEmDinheiro,
            'total_no_credito' => $this->totalNoCredito,
            'total_no_debito' => $this->totalNoDebito,
            'total_vendido' => $this->totalVendido,
            'quantidade_de_vendas' => $this->quantidadeDeVendas,
            'ticket_medio' => $this->ticketMedio,
            'maior_venda' => $this->maiorVenda,
            'menor_venda' => $this->menorVenda,
            'desconto_medio' => $this->descontoMedio,
            'desconto_maximo' => $this->descontoMaximo,
            'vendas_parceladas' => $this->vendasParceladas,
            'total_parcelado' => $this->totalParcelado,
            'vendas_a_vista' => $this->vendasAVista,
            'media_de_parcelas' => $this->mediaDeParcelas,
            'clientes_atendidos' => $this->clientesAtendidos,
            'quantidade_no_credito' => $this->quantidadeNoCredito,
            'quantidade_no_debito' => $this->quantidadeNoDebito,
            'quantidade_em_dinheiro' => $this->quantidadeEmDinheiro,
            'saldo_inicial' => $this->saldoInicial,
            'saldo_atual' => $this->saldoAtual,
            'caixa_aberto' => $this->caixaAberto,
            'total_de_sangrias' => $this->totalDeSangrias,
            'quantidade_de_sangrias' => $this->quantidadeDeSangrias,
            'maior_sangria' => $this->maiorSangria,
            'total_de_entradas' => $this->totalDeEntradas,
            'quantidade_de_entradas' => $this->quantidadeDeEntradas,
            'maior_entrada' => $this->maiorEntrada,
            'vendas_do_mes' => $this->vendasDoMes,
            'quantidade_de_vendas_do_mes' => $this->quantidadeDeVendasDoMes,
            'vendas_do_ano' => $this->vendasDoAno,
        ];
    }
}
