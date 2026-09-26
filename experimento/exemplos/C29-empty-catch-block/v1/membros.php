<?php

/**
 * Fragmento da variante C29/v1: bloco catch sem nada dentro.
 */
class Fragmento
{
    /**
     * Registra uma entrada de dinheiro avulsa no caixa do dia.
     *
     * @param Request $request Requisição com o valor e a descrição da entrada.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function registrarEntradaAvulsa(Request $request)
    {
        $entrada = new Entrada_caixa();
        $entrada->valor = $request->valor;
        $entrada->descricao = $request->descricao;
        try {
            $entrada->save();
        } catch (QueryException $e) {
        }
        return response()->json(['success' => 'true']);
    }
}
