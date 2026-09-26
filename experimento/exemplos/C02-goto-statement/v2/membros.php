<?php

/**
 * Fragmento da variante C02/v2: goto para trás, implementando um laço de repetição.
 */
class Fragmento
{
    /**
     * Grava uma sangria, repetindo a gravação até o número de tentativas indicado.
     *
     * @param Sangria $sangria    sangria a gravar
     * @param int     $tentativas número máximo de tentativas
     * @return bool
     */
    public function gravarSangriaComRepeticao(Sangria $sangria, $tentativas)
    {
        $feitas = 0;
        tentar:
        $feitas++;
        $gravou = $sangria->save();
        if (!$gravou && $feitas < $tentativas) {
            goto tentar;
        }

        return $gravou;
    }
}
