<?php

/**
 * Fragmento da variante C18/v1: método com complexidade ciclomática 11
 * (indicadorDoPainel: um switch com 10 casos e default).
 */
class Fragmento
{
    /**
     * Calcula um indicador do painel do caixa, escolhido pelo nome.
     *
     * @param string $indicador Nome do indicador pedido pela tela do painel.
     *
     * @return mixed Valor do indicador, ou null se o nome não for conhecido.
     */
    public function indicadorDoPainel($indicador)
    {
        switch ($indicador) {
            case 'vendas_do_dia':
                $valor = Transacoes::today()->sum('total');
                break;
            case 'vendas_do_mes':
                $valor = Transacoes::month()->sum('total');
                break;
            case 'vendas_do_ano':
                $valor = Transacoes::year()->sum('total');
                break;
            case 'credito_do_dia':
                $valor = Transacoes::totalCreditoDay();
                break;
            case 'debito_do_dia':
                $valor = Transacoes::totalDebitoDay();
                break;
            case 'sangrias_do_dia':
                $valor = Sangria::today()->sum('valor');
                break;
            case 'entradas_do_dia':
                $valor = Entrada_caixa::today()->sum('valor');
                break;
            case 'faturamento_do_ano':
                $valor = Transacoes::Faturamento(date('Y'));
                break;
            case 'unidades_em_estoque':
                $valor = Estoque::Total();
                break;
            case 'valor_do_estoque':
                $valor = Estoque::valorTotalRS();
                break;
            default:
                $valor = null;
        }

        return $valor;
    }
}
