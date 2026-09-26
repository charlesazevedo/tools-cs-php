<?php

namespace App\Experimento\C04\V2;

use App\Http\Controllers\Controller;
use App\Models\Venda;

/**
 * Consulta de vendas do PDV, protegida pelo privilégio ver_vendas.
 */
class ConsultaDeVendasController extends Controller
{
    /**
     * Restringe as ações deste controlador a quem tem o privilégio ver_vendas.
     */
    public function __construct()
    {
        $this->middleware('can:ver_vendas');
    }

    /**
     * Devolve os dados da venda em JSON.
     *
     * @param Venda $venda registro a exibir
     * @return \Illuminate\Http\JsonResponse
     */
    public function exibir(Venda $venda)
    {
        return response()->json($venda);
    }
}
