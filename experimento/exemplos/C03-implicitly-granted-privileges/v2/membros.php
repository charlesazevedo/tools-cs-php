<?php

/**
 * Fragmento da variante C03/v2: ação protegida pelo privilégio de consulta chama
 * um método auxiliar que exclui registros, operação que exige o privilégio de exclusão.
 */
class Fragmento
{
    /**
     * Exclui uma entrada de caixa; exige o privilégio caixa.excluir.
     *
     * @param Entrada_caixa $entrada entrada a excluir
     * @return \Illuminate\Http\JsonResponse
     */
    public function excluirEntradaDeCaixa(Entrada_caixa $entrada)
    {
        $this->authorize('caixa.excluir');
        $entrada->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Exibe uma entrada de caixa; exige o privilégio caixa.visualizar.
     *
     * @param Entrada_caixa $entrada entrada a exibir
     * @return \Illuminate\Http\JsonResponse
     */
    public function exibirEntradaDeCaixa(Entrada_caixa $entrada)
    {
        $this->authorize('caixa.visualizar');
        $descartada = $this->descartarEntradaZerada($entrada);

        return response()->json(['entrada' => $entrada, 'descartada' => $descartada]);
    }

    /**
     * Exclui a entrada quando o valor dela é zero.
     *
     * @param Entrada_caixa $entrada entrada a verificar
     * @return bool
     */
    private function descartarEntradaZerada(Entrada_caixa $entrada)
    {
        if ((float) $entrada->valor !== 0.0) {
            return false;
        }
        $entrada->delete();

        return true;
    }
}
