<?php

/**
 * Fragmento da variante C13/v1: método com 11 parâmetros (registrarVendaDeItem).
 */
class Fragmento
{
    /**
     * Registra uma venda de um único item: grava a transação e a linha de venda
     * e baixa o estoque da variação vendida.
     *
     * @param string $cliente          CPF do cliente.
     * @param string $data             Data da venda (Y-m-d).
     * @param string $pagamento        Forma de pagamento (DI, CR ou DE).
     * @param int    $parcelas         Número de parcelas.
     * @param float  $valorParcelas    Valor de cada parcela.
     * @param int    $desconto         Desconto concedido, em %.
     * @param float  $total            Valor total da venda.
     * @param string $codigoEstoque    Código do produto no estoque.
     * @param int    $codigoEstoqueAux Identificador da variação (cor e tamanho).
     * @param int    $quantidade       Quantidade vendida.
     * @param float  $valorVenda       Preço unitário praticado.
     *
     * @return int Identificador da transação gravada.
     */
    public function registrarVendaDeItem(
        $cliente,
        $data,
        $pagamento,
        $parcelas,
        $valorParcelas,
        $desconto,
        $total,
        $codigoEstoque,
        $codigoEstoqueAux,
        $quantidade,
        $valorVenda
    ) {
        $transacao = new Transacoes();
        $transacao->cliente = $cliente;
        $transacao->data = $data;
        $transacao->pagamento = $pagamento;
        $transacao->parcelas = $parcelas;
        $transacao->valor_parcelas = $valorParcelas;
        $transacao->desconto = $desconto;
        $transacao->total = $total;
        $transacao->save();

        $venda = new Venda();
        $venda->transacao = $transacao->id;
        $venda->codigo_estoque = $codigoEstoque;
        $venda->codigo_estoque_aux = $codigoEstoqueAux;
        $venda->quantidade = $quantidade;
        $venda->valor_venda = $valorVenda;
        $venda->save();

        \App\Models\Estoque\Estoque_aux::getOff($codigoEstoqueAux, $quantidade);

        return $transacao->id;
    }
}
