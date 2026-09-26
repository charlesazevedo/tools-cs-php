<?php

namespace App\Experimento\C04\V2;

use App\Http\Controllers\Controller;
use App\Models\Cliente;

/**
 * Consulta de clientes do PDV, protegida pelo privilégio ver_clientes.
 */
class ConsultaDeClientesController extends Controller
{
    /**
     * Restringe as ações deste controlador a quem tem o privilégio ver_clientes.
     */
    public function __construct()
    {
        $this->middleware('can:ver_clientes');
    }

    /**
     * Devolve os dados do cliente em JSON.
     *
     * @param Cliente $cliente registro a exibir
     * @return \Illuminate\Http\JsonResponse
     */
    public function exibir(Cliente $cliente)
    {
        return response()->json($cliente);
    }
}
