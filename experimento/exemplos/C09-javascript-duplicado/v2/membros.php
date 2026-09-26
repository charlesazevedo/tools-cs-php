<?php

/**
 * Fragmento da variante C09/v2: o mesmo JavaScript de impressão embutido em
 * strings PHP de dois métodos do controlador.
 */
class Fragmento
{
    /**
     * Devolve o comprovante de uma sangria, pronto para impressão.
     *
     * @param Sangria $sangria sangria registrada
     * @return \Illuminate\Http\Response
     */
    public function imprimirComprovanteDeSangria(Sangria $sangria)
    {
        $descricao = e($sangria->descricao);
        $valor = number_format($sangria->valor, 2, ',', '.');
        $html = <<<HTML
<h3>Comprovante de retirada</h3>
<p>Motivo: {$descricao}</p>
<p>Valor retirado: R$ {$valor}</p>
<button id="imprimir-comprovante" type="button">Imprimir</button>
<script>
    (function () {
        var botao = document.getElementById('imprimir-comprovante');
        botao.addEventListener('click', function () {
            botao.disabled = true;
            window.print();
        });
        window.addEventListener('afterprint', function () {
            botao.disabled = false;
            if (window.opener) {
                window.close();
            }
        });
    })();
</script>
HTML;

        return response($html);
    }

    /**
     * Devolve o comprovante de uma entrada de caixa, pronto para impressão.
     *
     * @param Entrada_caixa $entrada entrada registrada
     * @return \Illuminate\Http\Response
     */
    public function imprimirComprovanteDeEntrada(Entrada_caixa $entrada)
    {
        $origem = e($entrada->descricao);
        $data = $entrada->created_at;
        $html = <<<HTML
<h3>Comprovante de entrada no caixa</h3>
<p>Origem: {$origem}</p>
<p>Registrada em: {$data}</p>
<button id="imprimir-comprovante" type="button">Imprimir</button>
<script>
    (function () {
        var botao = document.getElementById('imprimir-comprovante');
        botao.addEventListener('click', function () {
            botao.disabled = true;
            window.print();
        });
        window.addEventListener('afterprint', function () {
            botao.disabled = false;
            if (window.opener) {
                window.close();
            }
        });
    })();
</script>
HTML;

        return response($html);
    }
}
