<?php

/**
 * Fragmento da variante C05/v3: JavaScript embutido numa string PHP devolvida
 * por um método do controlador.
 */
class Fragmento
{
    /**
     * Devolve o aviso de caixa fechado, que redireciona para a abertura do caixa.
     *
     * @return \Illuminate\Http\Response
     */
    public function exibirAvisoDeCaixaFechado()
    {
        $rotaAbertura = route('caixa.abrir');
        $html = <<<HTML
<div id="aviso-caixa" class="alert alert-warning">O caixa está fechado. Você será levado à abertura do caixa.</div>
<script>
    setTimeout(function () {
        window.location.href = '{$rotaAbertura}';
    }, 3000);
</script>
HTML;

        return response($html);
    }
}
