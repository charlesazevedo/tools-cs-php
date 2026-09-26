<?php

/**
 * Fragmento da variante C31/v2: comentário sem @return em método que declara
 * tipo de retorno nativo.
 */
class Fragmento
{
    /**
     * Monta a descrição de uma sangria para o histórico do caixa.
     *
     * @param string $descricao Motivo informado na sangria.
     * @param float  $valor     Valor retirado.
     */
    public function descreverSangria(string $descricao, float $valor): string
    {
        $valorFormatado = number_format($valor, 2, ',', '.');

        return sprintf('%s (R$ %s)', $descricao, $valorFormatado);
    }
}
