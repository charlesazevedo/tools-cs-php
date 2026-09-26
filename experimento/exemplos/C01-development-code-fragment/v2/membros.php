<?php

/**
 * Fragmento da variante C01/v2: print_r() esquecido num método.
 */
class Fragmento
{
    /**
     * Agrupa as entradas de caixa por descrição, somando os valores de cada grupo.
     *
     * @param array $entradas lista de entradas, cada uma com as chaves 'descricao' e 'valor'
     * @return array
     */
    public function agruparEntradasPorDescricao(array $entradas)
    {
        $resumo = [];
        foreach ($entradas as $entrada) {
            $descricao = $entrada['descricao'];
            $anterior = isset($resumo[$descricao]) ? $resumo[$descricao] : 0.0;
            $resumo[$descricao] = $anterior + (float) $entrada['valor'];
        }
        print_r($resumo);

        return $resumo;
    }
}
