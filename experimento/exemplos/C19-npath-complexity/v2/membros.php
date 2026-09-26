<?php

/**
 * Fragmento da variante C19/v2: método com NPath 512 e complexidade
 * ciclomática 10 (filtrarTransacoes: 9 if independentes em sequência).
 */
class Fragmento
{
    /**
     * Lista as transações que atendem aos filtros da tela de histórico; cada
     * filtro só é aplicado quando vem preenchido na requisição.
     *
     * @param Request $request Requisição com os filtros.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function filtrarTransacoes(Request $request)
    {
        $consulta = Transacoes::query();

        if ($request->filled('cliente')) {
            $consulta->where('cliente', $request->input('cliente'));
        }
        if ($request->filled('pagamento')) {
            $consulta->where('pagamento', $request->input('pagamento'));
        }
        if ($request->filled('inicio')) {
            $consulta->where('data', '>=', $request->input('inicio'));
        }
        if ($request->filled('fim')) {
            $consulta->where('data', '<=', $request->input('fim'));
        }
        if ($request->filled('parcelas')) {
            $consulta->where('parcelas', $request->input('parcelas'));
        }
        if ($request->filled('desconto_minimo')) {
            $descontoMinimo = $request->input('desconto_minimo');
            $consulta->where('desconto', '>=', $descontoMinimo);
        }
        if ($request->filled('total_minimo')) {
            $consulta->where('total', '>=', $request->input('total_minimo'));
        }
        if ($request->filled('total_maximo')) {
            $consulta->where('total', '<=', $request->input('total_maximo'));
        }
        if ($request->filled('limite')) {
            $consulta->limit($request->input('limite'));
        }

        return $consulta->get();
    }
}
