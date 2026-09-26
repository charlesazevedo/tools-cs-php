<?php

namespace App\Experimento\C04\V2;

use App\Http\Controllers\Controller;
use App\Models\Caixa;

/**
 * Consulta de caixa do PDV, protegida pelo privilégio ver_caixa.
 */
class ConsultaDeCaixaController extends Controller
{
    /**
     * Restringe as ações deste controlador a quem tem o privilégio ver_caixa.
     */
    public function __construct()
    {
        $this->middleware('can:ver_caixa');
    }

    /**
     * Devolve os dados do caixa em JSON.
     *
     * @param Caixa $caixa registro a exibir
     * @return \Illuminate\Http\JsonResponse
     */
    public function exibir(Caixa $caixa)
    {
        return response()->json($caixa);
    }
}
