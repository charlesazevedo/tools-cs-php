<?php

/**
 * Fragmento da variante C02/v1: goto para a frente, para sair de laços aninhados.
 */
class Fragmento
{
    /**
     * Procura, nas transações informadas, o primeiro item vendido do produto indicado.
     *
     * @param array  $transacoes lista de transações, cada uma com a lista de itens na chave 'itens'
     * @param string $codigo     código do produto no estoque
     * @return array
     */
    public function localizarPrimeiraVendaDoProduto(array $transacoes, $codigo)
    {
        $encontrado = [];
        foreach ($transacoes as $transacao) {
            foreach ($transacao['itens'] as $item) {
                if ($item['codigo_estoque'] === $codigo) {
                    $encontrado = $item;
                    goto fim;
                }
            }
        }
        fim:
        return $encontrado;
    }
}
