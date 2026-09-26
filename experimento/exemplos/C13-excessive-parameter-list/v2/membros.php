<?php

/**
 * Fragmento da variante C13/v2: método com 20 parâmetros
 * (registrarVendaComCadastroDeCliente).
 */
class Fragmento
{
    /**
     * Registra uma venda de um único item para um cliente novo: cadastra o
     * cliente, grava a transação e a linha de venda e baixa o estoque da
     * variação vendida.
     *
     * @param string $nome             Nome do cliente.
     * @param string $cpf              CPF do cliente.
     * @param string $sexo             Sexo do cliente (M, F ou I).
     * @param string $nascimento       Data de nascimento do cliente.
     * @param string $telefone         Telefone do cliente.
     * @param string $endereco         Endereço do cliente.
     * @param string $bairro           Bairro do cliente.
     * @param string $cidade           Cidade do cliente.
     * @param string $estado           Estado (UF) do cliente.
     * @param string $cep              CEP do cliente.
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
    public function registrarVendaComCadastroDeCliente(
        $nome,
        $cpf,
        $sexo,
        $nascimento,
        $telefone,
        $endereco,
        $bairro,
        $cidade,
        $estado,
        $cep,
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
        $cliente = new \App\Models\Cliente();
        $cliente->nome = $nome;
        $cliente->CPF = $cpf;
        $cliente->sexo = $sexo;
        $cliente->nascimento = $nascimento;
        $cliente->telefone = $telefone;
        $cliente->endereco = $endereco;
        $cliente->bairro = $bairro;
        $cliente->cidade = $cidade;
        $cliente->estado = $estado;
        $cliente->cep = $cep;
        $cliente->save();

        $transacao = new Transacoes();
        $transacao->cliente = $cpf;
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
