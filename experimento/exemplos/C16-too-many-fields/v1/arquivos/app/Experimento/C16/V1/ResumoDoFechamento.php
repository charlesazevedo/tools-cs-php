<?php

namespace App\Experimento\C16\V1;

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
        ];
    }
}
