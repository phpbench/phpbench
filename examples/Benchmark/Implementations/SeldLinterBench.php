<?php

namespace PhpBench\Examples\Benchmark\Implementations;

// section: all
use PhpBench\Config\ConfigLinter;
use PhpBench\Config\Linter\SeldLinter;

class SeldLinterBench extends LinterBenchCase
{
    public function createLinter(): ConfigLinter
    {
        return new SeldLinter();
    }
}
// endsection: all
