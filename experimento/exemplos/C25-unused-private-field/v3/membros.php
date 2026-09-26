<?php

/**
 * Fragmento da variante C25/v3: propriedade privada escrita no construtor e
 * nunca lida.
 */
class Fragmento
{
    /**
     * Data (AAAA-MM-DD) em que o controlador foi criado.
     *
     * @var string
     */
    private $dataDeAbertura;

    /**
     * Registra a data corrente na criação do controlador.
     */
    public function __construct()
    {
        $this->dataDeAbertura = date('Y-m-d');
    }
}
