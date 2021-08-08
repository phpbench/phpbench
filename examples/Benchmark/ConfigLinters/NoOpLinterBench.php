<?php

namespace PhpBench\Examples\Benchmark\ConfigLinters;

use PhpBench\Config\ConfigLinter;
use PhpBench\Examples\Benchmark\ConfigLinters\LinterBenchCase;
use PhpBench\Examples\Benchmark\ConfigLinters\NoOpLinter;

class NoOpLinterBench extends LinterBenchCase
{
    public function createLinter(): ConfigLinter
    {
        return new NoOpLinter();
    }
}
