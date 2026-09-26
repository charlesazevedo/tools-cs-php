<?php

/**
 * Fragmento da variante C28/v1: último parâmetro de um método privado nunca usado.
 */
class Fragmento
{
    /**
     * Devolve a descrição que a sangria terá no histórico do caixa.
     *
     * @param Request $request Requisição com a descrição e o valor da sangria.
     *
     * @return string
     */
    public function descricaoDaSangria(Request $request)
    {
        return $this->montarDescricaoDeSangria($request->descricao, $request->valor);
    }

    /**
     * Monta a descrição de uma sangria para o histórico do caixa.
     *
     * @param string $descricao Motivo da retirada.
     * @param string $valor     Valor retirado, como digitado.
     *
     * @return string
     */
    private function montarDescricaoDeSangria($descricao, $valor)
    {
        return 'Sangria: ' . trim($descricao);
    }
}
