<?php

namespace PhpBench\Benchmark;

use PhpBench\Math\Distribution;

interface AssertionInterface
{
    public function assert(string $expression, Distribution $distribution);
}
