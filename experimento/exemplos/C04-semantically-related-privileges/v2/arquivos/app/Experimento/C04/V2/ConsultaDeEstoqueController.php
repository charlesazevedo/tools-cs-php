<?php

namespace App\Experimento\C04\V2;

use App\Http\Controllers\Controller;
use App\Models\Estoque;

/**
 * Consulta de estoque do PDV, protegida pelo privilégio ver_estoque.
 */
class ConsultaDeEstoqueController extends Controller
{
    /**
     * Restringe as ações deste controlador a quem tem o privilégio ver_estoque.
     */
    public function __construct()
    {
        $this->middleware('can:ver_estoque');
    }

    /**
     * Devolve os dados do produto em JSON.
     *
     * @param Estoque $produto registro a exibir
     * @return \Illuminate\Http\JsonResponse
     */
    public function exibir(Estoque $produto)
    {
        return response()->json($produto);
    }
}
