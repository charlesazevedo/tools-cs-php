<?php

namespace App\Experimento\C17\V2;

use App\Models\Caixa;
use App\Models\Cliente;
use App\Models\Entrada_caixa;
use App\Models\Estoque;
use App\Models\Estoque\Estoque_aux;
use App\Models\Sangria;
use App\Models\Transacoes;
use App\Models\Venda;

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
     * Número de unidades vendidas no dia.
     *
     * @var int
     */
    public $itensVendidosHoje;

    /**
     * Número de produtos distintos vendidos no dia.
     *
     * @var int
     */
    public $produtosDistintosVendidos;

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
     * Soma de todas as vendas do dia, formatado para exibição.
     *
     * @var string
     */
    public $totalVendidoFormatado;

    /**
     * Valor médio das vendas do dia, formatado para exibição.
     *
     * @var string
     */
    public $ticketMedioFormatado;

    /**
     * Valor da maior venda do dia, formatado para exibição.
     *
     * @var string
     */
    public $maiorVendaFormatado;

    /**
     * Valor da menor venda do dia, formatado para exibição.
     *
     * @var string
     */
    public $menorVendaFormatado;

    /**
     * Desconto médio concedido nas vendas do dia, em %, formatado para
     * exibição.
     *
     * @var string
     */
    public $descontoMedioFormatado;

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
     * Soma das vendas do dia pagas em dinheiro, formatado para exibição.
     *
     * @var string
     */
    public $totalEmDinheiroFormatado;

    /**
     * Soma das vendas do dia no cartão de crédito, formatado para exibição.
     *
     * @var string
     */
    public $totalNoCreditoFormatado;

    /**
     * Soma das vendas do dia no cartão de débito, formatado para exibição.
     *
     * @var string
     */
    public $totalNoDebitoFormatado;

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
     * Maior valor de parcela entre as vendas do dia.
     *
     * @var float
     */
    public $maiorParcela;

    /**
     * Soma das vendas do dia em mais de uma parcela, formatado para exibição.
     *
     * @var string
     */
    public $totalParceladoFormatado;

    /**
     * Maior valor de parcela entre as vendas do dia, formatado para exibição.
     *
     * @var string
     */
    public $maiorParcelaFormatado;

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
     * Saldo com que o caixa do dia foi aberto, formatado para exibição.
     *
     * @var string
     */
    public $saldoInicialFormatado;

    /**
     * Saldo atual do caixa do dia, formatado para exibição.
     *
     * @var string
     */
    public $saldoAtualFormatado;

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
     * Soma das sangrias do dia, formatado para exibição.
     *
     * @var string
     */
    public $totalDeSangriasFormatado;

    /**
     * Valor da maior sangria do dia, formatado para exibição.
     *
     * @var string
     */
    public $maiorSangriaFormatado;

    /**
     * Soma das entradas de caixa do dia, formatado para exibição.
     *
     * @var string
     */
    public $totalDeEntradasFormatado;

    /**
     * Valor da maior entrada de caixa do dia, formatado para exibição.
     *
     * @var string
     */
    public $maiorEntradaFormatado;

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
     * Soma das vendas do mês corrente, formatado para exibição.
     *
     * @var string
     */
    public $vendasDoMesFormatado;

    /**
     * Soma das sangrias do mês corrente, formatado para exibição.
     *
     * @var string
     */
    public $sangriasDoMesFormatado;

    /**
     * Soma das entradas de caixa do mês corrente, formatado para exibição.
     *
     * @var string
     */
    public $entradasDoMesFormatado;

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
     * Soma das vendas do mês no cartão de crédito, formatado para exibição.
     *
     * @var string
     */
    public $creditoDoMesFormatado;

    /**
     * Soma das vendas do mês no cartão de débito, formatado para exibição.
     *
     * @var string
     */
    public $debitoDoMesFormatado;

    /**
     * Soma das vendas do mês pagas em dinheiro, formatado para exibição.
     *
     * @var string
     */
    public $dinheiroDoMesFormatado;

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
     * Soma das sangrias do ano corrente.
     *
     * @var float
     */
    public $sangriasDoAno;

    /**
     * Soma das entradas de caixa do ano corrente.
     *
     * @var float
     */
    public $entradasDoAno;

    /**
     * Soma das vendas do ano corrente, formatado para exibição.
     *
     * @var string
     */
    public $vendasDoAnoFormatado;

    /**
     * Faturamento do ano anterior, formatado para exibição.
     *
     * @var string
     */
    public $faturamentoAnteriorFormatado;

    /**
     * Soma das sangrias do ano corrente, formatado para exibição.
     *
     * @var string
     */
    public $sangriasDoAnoFormatado;

    /**
     * Soma das entradas de caixa do ano corrente, formatado para exibição.
     *
     * @var string
     */
    public $entradasDoAnoFormatado;

    /**
     * Número de unidades em estoque, somadas as variações.
     *
     * @var int
     */
    public $unidadesEmEstoque;

    /**
     * Valor do estoque a preço de venda, já formatado.
     *
     * @var string
     */
    public $valorDoEstoque;

    /**
     * Número de produtos sem unidades em estoque.
     *
     * @var int
     */
    public $produtosSemEstoque;

    /**
     * Margem de lucro média dos produtos, em %.
     *
     * @var float
     */
    public $margemMediaDeLucro;

    /**
     * Preço de custo médio dos produtos.
     *
     * @var float
     */
    public $precoMedioDeCusto;

    /**
     * Preço de venda médio dos produtos.
     *
     * @var float
     */
    public $precoMedioDeVenda;

    /**
     * Número de produtos cadastrados.
     *
     * @var int
     */
    public $produtosCadastrados;

    /**
     * Número de variações (cor e tamanho) com estoque.
     *
     * @var int
     */
    public $variacoesDisponiveis;

    /**
     * Margem de lucro média dos produtos, em %, formatado para exibição.
     *
     * @var string
     */
    public $margemMediaDeLucroFormatado;

    /**
     * Preço de custo médio dos produtos, formatado para exibição.
     *
     * @var string
     */
    public $precoMedioDeCustoFormatado;

    /**
     * Preço de venda médio dos produtos, formatado para exibição.
     *
     * @var string
     */
    public $precoMedioDeVendaFormatado;

    /**
     * Número de clientes cadastrados.
     *
     * @var int
     */
    public $clientesCadastrados;

    /**
     * Número de clientes cadastrados no mês corrente.
     *
     * @var int
     */
    public $clientesNovosDoMes;

    /**
     * Carrega os indicadores de vendas e descontos do dia.
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
        $this->itensVendidosHoje = Venda::query()
            ->whereIn('transacao', Transacoes::today()->pluck('id'))
            ->sum('quantidade');
        $this->produtosDistintosVendidos = Venda::query()
            ->whereIn('transacao', Transacoes::today()->pluck('id'))
            ->distinct()
            ->count('codigo_estoque');
        $this->descontoMedio = Transacoes::today()->avg('desconto');
        $this->descontoMaximo = Transacoes::today()->max('desconto');
        $this->totalVendidoFormatado = $this->moeda($this->totalVendido);
        $this->ticketMedioFormatado = $this->moeda($this->ticketMedio);
        $this->maiorVendaFormatado = $this->moeda($this->maiorVenda);
        $this->menorVendaFormatado = $this->moeda($this->menorVenda);
        $this->descontoMedioFormatado = $this->percentual($this->descontoMedio);

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
        $this->totalEmDinheiroFormatado = $this->moeda($this->totalEmDinheiro);
        $this->totalNoCreditoFormatado = $this->moeda($this->totalNoCredito);
        $this->totalNoDebitoFormatado = $this->moeda($this->totalNoDebito);

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
        $this->maiorParcela = Transacoes::today()->max('valor_parcelas');
        $this->totalParceladoFormatado = $this->moeda($this->totalParcelado);
        $this->maiorParcelaFormatado = $this->moeda($this->maiorParcela);

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
        $this->saldoInicialFormatado = $this->moeda($this->saldoInicial);
        $this->saldoAtualFormatado = $this->moeda($this->saldoAtual);

        return $this;
    }

    /**
     * Carrega as sangrias e as entradas de caixa do dia.
     *
     * @return $this
     */
    public function carregarMovimentacoes()
    {
        $this->totalDeSangrias = Sangria::today()->sum('valor');
        $this->quantidadeDeSangrias = Sangria::today()->count();
        $this->maiorSangria = Sangria::today()->max('valor');
        $this->totalDeEntradas = Entrada_caixa::today()->sum('valor');
        $this->quantidadeDeEntradas = Entrada_caixa::today()->count();
        $this->maiorEntrada = Entrada_caixa::today()->max('valor');
        $this->totalDeSangriasFormatado = $this->moeda($this->totalDeSangrias);
        $this->maiorSangriaFormatado = $this->moeda($this->maiorSangria);
        $this->totalDeEntradasFormatado = $this->moeda($this->totalDeEntradas);
        $this->maiorEntradaFormatado = $this->moeda($this->maiorEntrada);

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
        $this->vendasDoMesFormatado = $this->moeda($this->vendasDoMes);
        $this->sangriasDoMesFormatado = $this->moeda($this->sangriasDoMes);
        $this->entradasDoMesFormatado = $this->moeda($this->entradasDoMes);

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
        $this->creditoDoMesFormatado = $this->moeda($this->creditoDoMes);
        $this->debitoDoMesFormatado = $this->moeda($this->debitoDoMes);
        $this->dinheiroDoMesFormatado = $this->moeda($this->dinheiroDoMes);

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
        $this->sangriasDoAno = Sangria::whereYear('data', date('Y'))
            ->sum('valor');
        $this->entradasDoAno = Entrada_caixa::query()
            ->whereYear('created_at', date('Y'))
            ->sum('valor');
        $this->vendasDoAnoFormatado = $this->moeda($this->vendasDoAno);
        $this->faturamentoAnteriorFormatado = $this->moeda(
            $this->faturamentoAnterior
        );
        $this->sangriasDoAnoFormatado = $this->moeda($this->sangriasDoAno);
        $this->entradasDoAnoFormatado = $this->moeda($this->entradasDoAno);

        return $this;
    }

    /**
     * Carrega a situação do estoque.
     *
     * @return $this
     */
    public function carregarEstoque()
    {
        $this->unidadesEmEstoque = Estoque::Total();
        $this->valorDoEstoque = Estoque::valorTotalRS();
        $this->produtosSemEstoque = Estoque::where('estoque', '<=', 0)->count();
        $this->margemMediaDeLucro = Estoque::avg('lucro');
        $this->precoMedioDeCusto = Estoque::avg('preco_custo');
        $this->precoMedioDeVenda = Estoque::avg('preco');
        $this->produtosCadastrados = Estoque::count();
        $this->variacoesDisponiveis = Estoque_aux::where('estoque', '>', 0)
            ->count();
        $this->margemMediaDeLucroFormatado = $this->percentual(
            $this->margemMediaDeLucro
        );
        $this->precoMedioDeCustoFormatado = $this->moeda(
            $this->precoMedioDeCusto
        );
        $this->precoMedioDeVendaFormatado = $this->moeda(
            $this->precoMedioDeVenda
        );

        return $this;
    }

    /**
     * Carrega os indicadores do cadastro de clientes.
     *
     * @return $this
     */
    public function carregarClientes()
    {
        $this->clientesCadastrados = Cliente::count();
        $this->clientesNovosDoMes = Cliente::whereMonth('created_at', date('m'))
            ->count();

        return $this;
    }

    /**
     * Formata um valor em reais (1.234,56).
     *
     * @param float $valor Valor a formatar.
     *
     * @return string
     */
    private function moeda($valor)
    {
        return number_format($valor, 2, ',', '.');
    }

    /**
     * Formata um percentual com uma casa decimal (12,5%).
     *
     * @param float $valor Percentual a formatar.
     *
     * @return string
     */
    private function percentual($valor)
    {
        return number_format($valor, 1, ',', '.') . '%';
    }
}
