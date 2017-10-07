<?php

namespace PhpBench\Assertion;

use PhpBench\Registry\RegistrableInterface;
use PhpBench\Math\Distribution;
use PhpBench\Registry\Config;

interface Assertion extends RegistrableInterface
{
    public function assert(string $property, $value, Distribution $distribution, Config $config);
}
