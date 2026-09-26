<?php

namespace App\Experimento\C08\V2;

/**
 * Gera o JavaScript do formulário de sangria.
 *
 * O script manipula os elementos valor-sangria e saldo-restante, que são
 * gerados por FormularioDeSangria.
 */
class ScriptDoFormularioDeSangria
{
    /**
     * Monta o script que atualiza o saldo restante enquanto o valor é digitado.
     *
     * @return string
     */
    public function renderizar()
    {
        return <<<'HTML'
<script>
    document.getElementById('valor-sangria').addEventListener('input', function () {
        var saldo = parseFloat(this.getAttribute('data-saldo'));
        var retirada = parseFloat(this.value.replace(',', '.')) || 0;
        document.getElementById('saldo-restante').textContent = (saldo - retirada).toFixed(2).replace('.', ',');
    });
</script>
HTML;
    }
}
