<?php

namespace PhpBench\Examples\Benchmark\Implementations;

use PhpBench\Config\ConfigLinter;
use PhpBench\Config\Linter\SeldLinter;

class NoOpLinterBench extends LinterBenchCase
{
    public function createLinter(): ConfigLinter
    {
        return new NoOpLinter();
    }
}
