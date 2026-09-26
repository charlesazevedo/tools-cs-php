<?php

/**
 * Fragmento da variante C31/v1: comentário sem @return em método que retorna
 * valor e não declara tipo de retorno.
 */
class Fragmento
{
    /**
     * Monta a descrição de uma sangria para o histórico do caixa.
     *
     * @param string $descricao Motivo informado na sangria.
     * @param float  $valor     Valor retirado.
     */
    public function descreverSangria(string $descricao, float $valor)
    {
        $valorFormatado = number_format($valor, 2, ',', '.');

        return sprintf('%s (R$ %s)', $descricao, $valorFormatado);
    }
}
