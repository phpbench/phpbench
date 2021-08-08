<?php

namespace PhpBench\Examples\Benchmark\ConfigLinters;

// section: all
use PhpBench\Config\ConfigLinter;
use PhpBench\Config\Linter\SeldLinter;
use PhpBench\Examples\Benchmark\ConfigLinters\LinterBenchCase;

class SeldLinterBench extends LinterBenchCase
{
    public function createLinter(): ConfigLinter
    {
        return new SeldLinter();
    }
}
// endsection: all
