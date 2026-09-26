<?php

/**
 * Fragmento da variante C29/v2: bloco catch que só contém um comentário.
 */
class Fragmento
{
    /**
     * Marca o caixa do dia como aberto e volta à tela de abertura.
     *
     * @return \Illuminate\View\View
     */
    public function reabrirCaixaDoDia()
    {
        try {
            Sistema::setVal('caixa_aberto', true);
        } catch (QueryException $e) {
            // ignora: se falhar, o caixa continua fechado
        }
        return view('admin.caixa.abrir', ['aberto' => Caixa::checkOpen()]);
    }
}
