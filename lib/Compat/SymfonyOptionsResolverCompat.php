<?php

namespace PhpBench\Compat;

use Symfony\Component\OptionsResolver\OptionsResolver;

class SymfonyOptionsResolverCompat
{
    /**
     * @param array<string,string> $infoMap
     */
    public static function setInfos(OptionsResolver $resolver, array $infoMap): void
    {
        foreach ($infoMap as $option => $help) {
            $resolver->setInfo($option, $help);
        }
    }
}
