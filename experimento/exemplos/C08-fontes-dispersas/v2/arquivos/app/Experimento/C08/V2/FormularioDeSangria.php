<?php

namespace App\Experimento\C08\V2;

/**
 * Gera o HTML do formulário de sangria.
 *
 * O comportamento do formulário (atualizar o saldo restante enquanto o valor é
 * digitado) fica em outra classe, ScriptDoFormularioDeSangria, que depende dos
 * ids dos elementos gerados aqui.
 */
class FormularioDeSangria
{
    /**
     * Gerador do script que acompanha o formulário.
     *
     * @var ScriptDoFormularioDeSangria
     */
    private $script;

    /**
     * Recebe o gerador do script do formulário.
     *
     * @param ScriptDoFormularioDeSangria $script gerador do script do formulário
     */
    public function __construct(ScriptDoFormularioDeSangria $script)
    {
        $this->script = $script;
    }

    /**
     * Monta o formulário de sangria seguido do script que o acompanha.
     *
     * @param float $saldo saldo atual do caixa
     * @return string
     */
    public function renderizar($saldo)
    {
        $html = '<form id="form-sangria" method="post">'
            . csrf_field()
            . '<label for="valor-sangria">Valor da retirada: R$</label>'
            . '<input id="valor-sangria" name="valor" class="form-control" data-saldo="' . $saldo . '" />'
            . '<p>Saldo restante: R$ <span id="saldo-restante">' . number_format($saldo, 2, ',', '.') . '</span></p>'
            . '<button type="submit" class="btn btn-success">Retirar</button>'
            . '</form>';

        return $html . $this->script->renderizar();
    }
}
