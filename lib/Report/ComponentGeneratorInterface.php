<?php

namespace PhpBench\Report;

use PhpBench\Data\DataFrame;
use PhpBench\Registry\RegistrableInterface;

interface ComponentGeneratorInterface extends RegistrableInterface
{
    /**
     * @param array<string,mixed> $config
     */
    public function generateComponent(DataFrame $dataFrame, array $config): ComponentInterface;
}
