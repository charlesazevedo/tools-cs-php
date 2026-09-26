<?php

namespace App\Experimento\C22\V1;

/**
 * Relatório do caixa de um dia. Cada subclasse acrescenta uma seção; a classe
 * concreta define o título.
 */
abstract class Relatorio
{
    /**
     * Data do relatório (Y-m-d).
     *
     * @var string
     */
    protected $data;

    /**
     * Cria o relatório de uma data.
     *
     * @param string $data Data do relatório (Y-m-d).
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Monta o relatório: título, data e seções.
     *
     * @return array
     */
    public function gerar()
    {
        return [
            'titulo' => $this->titulo(),
            'data' => $this->data,
            'secoes' => $this->secoes(),
        ];
    }

    /**
     * Título do relatório.
     *
     * @return string
     */
    abstract protected function titulo();

    /**
     * Seções do relatório; cada subclasse acrescenta as suas.
     *
     * @return array
     */
    protected function secoes()
    {
        return [];
    }
}
