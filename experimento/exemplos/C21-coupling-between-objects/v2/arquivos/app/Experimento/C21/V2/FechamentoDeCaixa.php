<?php

namespace App\Experimento\C21\V2;

use App\Models\Caixa;
use App\Models\Cliente;
use App\Models\Entrada_caixa;
use App\Models\Estoque;
use App\Models\Estoque\Categoria;
use App\Models\Estoque\Estoque_aux;
use App\Models\Estoque\Fornecedor;
use App\Models\Estoque\Marca;
use App\Models\Estoque\Unidade;
use App\Models\Sangria;
use App\Models\Sistema;
use App\Models\Transacoes;
use App\Models\Venda;
use App\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

/**
 * Fecha o caixa do dia: valida o valor contado pelo operador, grava-o, marca
 * o caixa como fechado, guarda o resumo em cache e o devolve.
 */
class FechamentoDeCaixa
{
    /**
     * Fecha o caixa do dia com o valor contado pelo operador.
     *
     * @param Request $request Requisição com o valor contado (campo contado).
     *
     * @return JsonResponse
     */
    public function fechar(Request $request)
    {
        $this->validar($request);

        $caixa = Caixa::today();
        if ($caixa === false) {
            return new JsonResponse([
                'success' => 'false',
                'message' => 'Não há caixa aberto hoje.',
            ]);
        }

        $informado = $request->input('contado');
        $contado = str_replace(['.', ','], ['', '.'], $informado);

        try {
            DB::transaction(function () use ($caixa, $contado) {
                $caixa->valor = $contado;
                $caixa->save();
                Sistema::setVal('caixa_aberto', false);
            });
        } catch (QueryException $e) {
            Log::error('Falha ao fechar o caixa: ' . $e->getMessage());

            return new JsonResponse([
                'success' => 'false',
                'message' => $e->getMessage(),
            ]);
        }

        $agora = new DateTime();
        $resumo = $this->resumo($caixa, $contado);
        $validade = Carbon::now()->addDay();
        Cache::put('fechamento-' . $caixa->data, $resumo, $validade);

        return new JsonResponse([
            'success' => 'true',
            'fechado_em' => $agora->format('d/m/Y H:i'),
            'resumo' => $resumo,
        ]);
    }

    /**
     * Confere se a requisição traz o valor contado.
     *
     * @param Request $request Requisição do fechamento.
     *
     * @return void
     *
     * @throws InvalidArgumentException Se o valor contado não foi informado.
     */
    private function validar(Request $request)
    {
        $regras = ['contado' => 'required'];
        $validacao = Validator::make($request->all(), $regras);
        if ($validacao->fails()) {
            throw new InvalidArgumentException('Informe o valor contado.');
        }
    }

    /**
     * Resumo do fechamento: operador, vendas, itens, clientes atendidos, saldo
     * esperado, diferença para o valor contado e situação do estoque.
     *
     * @param Caixa $caixa   Caixa do dia.
     * @param float $contado Valor contado pelo operador.
     *
     * @return array
     */
    private function resumo(Caixa $caixa, $contado)
    {
        $transacoes = Transacoes::today();
        $esperado = $this->saldoEsperado(
            $caixa,
            $transacoes,
            Sangria::today(),
            Entrada_caixa::today()
        );
        $itens = Venda::query()
            ->whereIn('transacao', $transacoes->pluck('id'))
            ->sum('quantidade');
        $clientes = Cliente::whereIn('CPF', $transacoes->pluck('cliente'))
            ->count();

        return [
            'operador' => $this->nomeDoOperador(),
            'vendas' => $transacoes->sum('total'),
            'itens' => $itens,
            'clientes_atendidos' => $clientes,
            'esperado' => $esperado,
            'diferenca' => $contado - $esperado,
            'estoque' => $this->resumoDoEstoque(),
        ];
    }

    /**
     * Saldo que deveria estar no caixa: saldo inicial, mais as vendas em
     * dinheiro e as entradas, menos as sangrias.
     *
     * @param Caixa      $caixa      Caixa do dia.
     * @param Collection $transacoes Vendas do dia.
     * @param Collection $sangrias   Sangrias do dia.
     * @param Collection $entradas   Entradas de caixa do dia.
     *
     * @return float
     */
    private function saldoEsperado(
        Caixa $caixa,
        Collection $transacoes,
        Collection $sangrias,
        Collection $entradas
    ) {
        return $caixa->inicial
            + $transacoes->where('pagamento', 'DI')->sum('total')
            + $entradas->sum('valor')
            - $sangrias->sum('valor');
    }

    /**
     * Situação do estoque no fechamento.
     *
     * @return array
     */
    private function resumoDoEstoque()
    {
        return [
            'unidades' => Estoque::Total(),
            'unidades_nas_variacoes' => Estoque_aux::Total(),
            'categorias' => Categoria::count(),
            'fornecedores' => Fornecedor::count(),
            'marcas' => Marca::count(),
            'unidades_de_medida' => Unidade::count(),
        ];
    }

    /**
     * Nome do usuário que está fechando o caixa.
     *
     * @return string
     */
    private function nomeDoOperador()
    {
        $nome = 'desconhecido';
        $usuario = Auth::user();
        if ($usuario instanceof User) {
            $nome = $usuario->name;
        }

        return $nome;
    }
}
