<?php

namespace App\Experimento\C08\V1;

/**
 * Gera o HTML da tabela de sangrias do dia.
 *
 * A abertura da tabela, as linhas e o fechamento (com o total) são produzidos
 * por métodos diferentes e só formam um elemento <table> completo depois de
 * concatenados em renderizar().
 */
class TabelaDeSangrias
{
    /**
     * Monta a tabela completa das sangrias informadas.
     *
     * @param array $sangrias lista de sangrias, cada uma com as chaves 'descricao' e 'valor'
     * @return string
     */
    public function renderizar(array $sangrias)
    {
        $total = array_sum(array_column($sangrias, 'valor'));

        return $this->abrirTabela() . $this->montarLinhas($sangrias) . $this->fecharTabela($total);
    }

    /**
     * Abre a tabela e escreve o cabeçalho.
     *
     * @return string
     */
    private function abrirTabela()
    {
        return '<table id="tabela-sangrias" class="table table-bordered">'
            . '<thead><tr><th>Descrição</th><th>Valor R$</th></tr></thead>'
            . '<tbody>';
    }

    /**
     * Escreve uma linha da tabela para cada sangria.
     *
     * @param array $sangrias lista de sangrias, cada uma com as chaves 'descricao' e 'valor'
     * @return string
     */
    private function montarLinhas(array $sangrias)
    {
        $linhas = '';
        foreach ($sangrias as $sangria) {
            $linhas .= '<tr><td>' . e($sangria['descricao']) . '</td>'
                . '<td>' . number_format($sangria['valor'], 2, ',', '.') . '</td></tr>';
        }

        return $linhas;
    }

    /**
     * Fecha o corpo da tabela, escreve o rodapé com o total e fecha a tabela.
     *
     * @param float $total soma dos valores das sangrias
     * @return string
     */
    private function fecharTabela($total)
    {
        return '</tbody>'
            . '<tfoot><tr><th>Total</th><th>' . number_format($total, 2, ',', '.') . '</th></tr></tfoot>'
            . '</table>';
    }
}
