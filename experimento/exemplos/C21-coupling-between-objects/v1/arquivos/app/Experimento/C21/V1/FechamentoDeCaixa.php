<?php

namespace App\Experimento\C21\V1;

use App\Models\Caixa;
use App\Models\Entrada_caixa;
use App\Models\Estoque;
use App\Models\Sangria;
use App\Models\Sistema;
use App\Models\Transacoes;
use App\Models\Venda;
use DateTime;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Fecha o caixa do dia: grava o valor contado pelo operador, marca o caixa
 * como fechado e devolve o resumo do fechamento.
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

        return new JsonResponse([
            'success' => 'true',
            'fechado_em' => $agora->format('d/m/Y H:i'),
            'resumo' => $this->resumo($caixa, $contado),
        ]);
    }

    /**
     * Resumo do fechamento: vendas, itens, saldo esperado, diferença para o
     * valor contado e unidades em estoque.
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

        return [
            'vendas' => $transacoes->sum('total'),
            'itens' => $itens,
            'esperado' => $esperado,
            'diferenca' => $contado - $esperado,
            'estoque' => Estoque::Total(),
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
}
