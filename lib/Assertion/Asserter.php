<?php

namespace PhpBench\Assertion;

use PhpBench\Registry\RegistrableInterface;
use PhpBench\Math\Distribution;
use PhpBench\Registry\Config;

interface Asserter extends RegistrableInterface
{
    public function assert(Distribution $distribution, Config $config);
}
