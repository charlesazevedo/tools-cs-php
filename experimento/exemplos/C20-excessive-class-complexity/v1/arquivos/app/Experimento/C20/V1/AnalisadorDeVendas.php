<?php

namespace App\Experimento\C20\V1;

use App\Models\Caixa;
use App\Models\Sangria;
use App\Models\Transacoes;

/**
 * Regras de análise das vendas e do caixa usadas nas telas gerenciais.
 */
class AnalisadorDeVendas
{
    /**
     * Classifica o valor de uma venda em uma faixa de ticket.
     *
     * @param float $total Valor total da venda.
     *
     * @return string
     */
    public function faixaDoTicket($total)
    {
        if ($total <= 10) {
            $faixa = 'até R$ 10,00';
        } elseif ($total <= 20) {
            $faixa = 'de R$ 10,01 a R$ 20,00';
        } elseif ($total <= 50) {
            $faixa = 'de R$ 20,01 a R$ 50,00';
        } elseif ($total <= 100) {
            $faixa = 'de R$ 50,01 a R$ 100,00';
        } elseif ($total <= 200) {
            $faixa = 'de R$ 100,01 a R$ 200,00';
        } elseif ($total <= 500) {
            $faixa = 'de R$ 200,01 a R$ 500,00';
        } elseif ($total <= 1000) {
            $faixa = 'de R$ 500,01 a R$ 1.000,00';
        } elseif ($total <= 2000) {
            $faixa = 'de R$ 1.000,01 a R$ 2.000,00';
        } else {
            $faixa = 'acima de R$ 2.000,00';
        }

        return $faixa;
    }

    /**
     * Nome do dia da semana de uma data.
     *
     * @param string $data Data (Y-m-d).
     *
     * @return string
     */
    public function nomeDoDiaDaSemana($data)
    {
        switch (date('w', strtotime($data))) {
            case '0':
                $nome = 'domingo';
                break;
            case '1':
                $nome = 'segunda-feira';
                break;
            case '2':
                $nome = 'terça-feira';
                break;
            case '3':
                $nome = 'quarta-feira';
                break;
            case '4':
                $nome = 'quinta-feira';
                break;
            case '5':
                $nome = 'sexta-feira';
                break;
            case '6':
                $nome = 'sábado';
                break;
            default:
                $nome = '';
        }

        return $nome;
    }

    /**
     * Alertas para o fechamento do caixa de um dia.
     *
     * @param string $data Data do caixa (Y-m-d).
     *
     * @return array Mensagens de alerta; vazio se estiver tudo em ordem.
     */
    public function alertasDoFechamento($data)
    {
        $caixa = Caixa::where('data', $data)->first();
        $transacoes = Transacoes::where('data', $data)->get();
        $sangrias = Sangria::where('data', $data)->get();
        $alertas = [];

        if ($caixa === null) {
            $alertas[] = 'O caixa não foi aberto no dia.';
        }
        if ($transacoes->count() === 0) {
            $alertas[] = 'Nenhuma venda registrada no dia.';
        }
        if ($sangrias->count() > 5) {
            $alertas[] = 'Mais de 5 sangrias no dia.';
        }
        if ($sangrias->sum('valor') > 1000) {
            $alertas[] = 'Sangrias acima de R$ 1.000,00 no dia.';
        }
        if ($transacoes->max('desconto') > 20) {
            $alertas[] = 'Desconto acima de 20% no dia.';
        }
        if ($transacoes->where('parcelas', '>', 6)->count() > 0) {
            $alertas[] = 'Venda em mais de 6 parcelas no dia.';
        }
        if ($transacoes->where('total', '>', 5000)->count() > 0) {
            $alertas[] = 'Venda acima de R$ 5.000,00 no dia.';
        }

        return $alertas;
    }

    /**
     * Confere os dados de uma venda antes de gravá-la.
     *
     * @param array $dados Cliente, pagamento, parcelas, desconto e total.
     *
     * @return array Mensagens de erro; vazio se a venda for válida.
     */
    public function validarVenda(array $dados)
    {
        $erros = [];

        if (empty($dados['cliente'])) {
            $erros[] = 'Informe o CPF do cliente.';
        }
        if (!in_array($dados['pagamento'], ['DI', 'CR', 'DE'], true)) {
            $erros[] = 'Forma de pagamento inválida.';
        }
        if ($dados['parcelas'] < 1) {
            $erros[] = 'A venda precisa de pelo menos uma parcela.';
        }
        if ($dados['parcelas'] > 12) {
            $erros[] = 'A venda pode ter no máximo 12 parcelas.';
        }
        if ($dados['desconto'] < 0) {
            $erros[] = 'O desconto não pode ser negativo.';
        }
        if ($dados['desconto'] > 100) {
            $erros[] = 'O desconto não pode passar de 100%.';
        }
        if ($dados['total'] <= 0) {
            $erros[] = 'O total da venda precisa ser positivo.';
        }

        return $erros;
    }

    /**
     * Totaliza as vendas por forma de pagamento e conta as vendas com
     * desconto e as parceladas.
     *
     * @param \Illuminate\Support\Collection $transacoes Vendas a totalizar.
     *
     * @return array
     */
    public function resumoPorFormaDePagamento($transacoes)
    {
        $resumo = [
            'dinheiro' => 0,
            'credito' => 0,
            'debito' => 0,
            'outros' => 0,
            'com_desconto' => 0,
            'parceladas' => 0,
        ];

        foreach ($transacoes as $transacao) {
            if ($transacao->pagamento === 'DI') {
                $resumo['dinheiro'] += $transacao->total;
            } elseif ($transacao->pagamento === 'CR') {
                $resumo['credito'] += $transacao->total;
            } elseif ($transacao->pagamento === 'DE') {
                $resumo['debito'] += $transacao->total;
            } else {
                $resumo['outros'] += $transacao->total;
            }
            if ($transacao->desconto > 0) {
                $resumo['com_desconto']++;
            }
            if ($transacao->parcelas > 1) {
                $resumo['parceladas']++;
            }
        }

        return $resumo;
    }

    /**
     * Taxa cobrada pela operadora de cartão, em % do valor da venda.
     *
     * @param string $pagamento Forma de pagamento (DI, CR ou DE).
     * @param int    $parcelas  Número de parcelas.
     *
     * @return float
     */
    public function taxaDoCartao($pagamento, $parcelas)
    {
        $taxa = 0.0;

        if ($pagamento === 'DE') {
            $taxa = 1.5;
        } elseif ($pagamento === 'CR') {
            if ($parcelas <= 1) {
                $taxa = 2.5;
            } elseif ($parcelas <= 3) {
                $taxa = 3.5;
            } elseif ($parcelas <= 6) {
                $taxa = 4.5;
            } elseif ($parcelas <= 10) {
                $taxa = 5.5;
            } else {
                $taxa = 6.5;
            }
        }

        return $taxa;
    }

    /**
     * Situação do estoque de um produto pelo número de unidades.
     *
     * @param int $unidades Unidades em estoque.
     *
     * @return string
     */
    public function statusDoEstoque($unidades)
    {
        if ($unidades <= 0) {
            $status = 'esgotado';
        } elseif ($unidades < 5) {
            $status = 'baixo';
        } elseif ($unidades < 20) {
            $status = 'normal';
        } else {
            $status = 'alto';
        }

        return $status;
    }
}
