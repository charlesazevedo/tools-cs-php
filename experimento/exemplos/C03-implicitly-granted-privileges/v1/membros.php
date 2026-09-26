<?php

/**
 * Fragmento da variante C03/v1: rotina protegida pelo privilégio de consulta
 * registra uma sangria, operação que exige o privilégio de sangria.
 */
class Fragmento
{
    /**
     * Registra uma sangria no caixa; exige o privilégio caixa.sangria.
     *
     * @param Request $request requisição com o valor e a descrição da retirada
     * @return \Illuminate\Http\JsonResponse
     */
    public function registrarSangriaAutorizada(Request $request)
    {
        $this->authorize('caixa.sangria');
        $gravou = $this->gravarRetiradaDoCaixa($request->input('valor'), $request->input('descricao'));

        return response()->json(['success' => $gravou]);
    }

    /**
     * Confere o saldo contado com o saldo do sistema; exige o privilégio caixa.visualizar.
     * Quando falta dinheiro na gaveta, registra a diferença como sangria.
     *
     * @param Request $request requisição com o saldo do sistema e o saldo contado
     * @return \Illuminate\Http\JsonResponse
     */
    public function conferirSaldoDoCaixa(Request $request)
    {
        $this->authorize('caixa.visualizar');
        $diferenca = (float) $request->input('saldo_sistema') - (float) $request->input('saldo_contado');
        if ($diferenca > 0) {
            $this->gravarRetiradaDoCaixa($diferenca, 'Quebra de caixa');
        }

        return response()->json(['diferenca' => $diferenca]);
    }

    /**
     * Grava uma retirada (sangria) com a data de hoje.
     *
     * @param float  $valor     valor retirado
     * @param string $descricao motivo da retirada
     * @return bool
     */
    private function gravarRetiradaDoCaixa($valor, $descricao)
    {
        $sangria = new Sangria();
        $sangria->data = date('Y-m-d');
        $sangria->valor = $valor;
        $sangria->descricao = $descricao;

        return $sangria->save();
    }
}
