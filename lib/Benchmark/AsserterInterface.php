<?php

namespace PhpBench\Benchmark;

use PhpBench\Benchmark\Assertion;
use PhpBench\Math\Distribution;

interface AsserterInterface
{
    public function assert(string $expression, Distribution $distribution);
}
