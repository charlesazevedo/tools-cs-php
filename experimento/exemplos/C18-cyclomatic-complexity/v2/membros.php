<?php

/**
 * Fragmento da variante C18/v2: método com complexidade ciclomática 20
 * (indicadorDoPainel: um switch com 19 casos e default).
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
            case 'caixa_aberto':
                $valor = Caixa::checkOpen();
                break;
            case 'saldo_inicial':
                $valor = Caixa::where('data', date('Y-m-d'))->value('inicial');
                break;
            case 'saldo_atual':
                $valor = Caixa::where('data', date('Y-m-d'))->value('valor');
                break;
            case 'quantidade_de_vendas':
                $valor = Transacoes::today()->count();
                break;
            case 'ticket_medio':
                $valor = Transacoes::today()->avg('total');
                break;
            case 'itens_vendidos':
                $valor = Venda::query()
                    ->whereIn('transacao', Transacoes::today()->pluck('id'))
                    ->sum('quantidade');
                break;
            case 'sangrias_do_mes':
                $valor = Sangria::whereMonth('data', date('m'))->sum('valor');
                break;
            case 'faturamento_do_ano_anterior':
                $valor = Transacoes::Faturamento(date('Y') - 1);
                break;
            case 'produtos_sem_estoque':
                $valor = Estoque::where('estoque', '<=', 0)->count();
                break;
            default:
                $valor = null;
        }

        return $valor;
    }
}
