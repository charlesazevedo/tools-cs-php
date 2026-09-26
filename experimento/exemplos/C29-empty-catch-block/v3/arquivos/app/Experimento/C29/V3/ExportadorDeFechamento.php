<?php

namespace App\Experimento\C29\V3;

use App\Models\Caixa;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

/**
 * Exportação do fechamento do caixa para arquivo texto.
 */
class ExportadorDeFechamento
{
    /**
     * Grava a data e o saldo do caixa do dia em um arquivo texto.
     *
     * @param string $caminho Caminho do arquivo de destino.
     *
     * @return void
     */
    public function exportar($caminho)
    {
        try {
            $caixa = Caixa::today();
            file_put_contents($caminho, $caixa->data . ';' . $caixa->valor);
        } catch (QueryException $e) {
            Log::warning('Caixa indisponível para exportação: ' . $e->getMessage());
        } catch (\Exception $e) {
        }
    }
}
