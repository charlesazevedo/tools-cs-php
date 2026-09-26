<?php

namespace App\Experimento\C04\V1;

use App\User;
use Illuminate\Contracts\Auth\Access\Gate;

/**
 * Registra no Gate os privilégios de exclusão do PDV, um para cada componente.
 *
 * Os quatro privilégios têm a mesma semântica (excluir registros, permitido só
 * aos gerentes), mas são definidos separadamente para caixa, vendas, estoque e
 * clientes.
 */
class PrivilegiosDeExclusao
{
    /**
     * Define os privilégios de exclusão no Gate da aplicação.
     *
     * @param Gate $gate Gate de autorização do Laravel
     * @return void
     */
    public function registrar(Gate $gate)
    {
        $gate->define('caixa.excluir', [$this, 'permitirGerente']);
        $gate->define('venda.excluir', [$this, 'permitirGerente']);
        $gate->define('estoque.excluir', [$this, 'permitirGerente']);
        $gate->define('cliente.excluir', [$this, 'permitirGerente']);
    }

    /**
     * Diz se o usuário está na lista de gerentes da configuração pdv.gerentes.
     *
     * @param User $usuario usuário autenticado
     * @return bool
     */
    public function permitirGerente(User $usuario)
    {
        return in_array($usuario->username, config('pdv.gerentes', []), true);
    }
}
