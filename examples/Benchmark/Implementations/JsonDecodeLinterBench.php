<?php

namespace PhpBench\Examples\Benchmark\Implementations;

use PhpBench\Config\ConfigLinter;
use PhpBench\Config\Linter\JsonDecodeLinter;
use PhpBench\Config\Linter\SeldLinter;

class JsonDecodeLinterBench extends LinterBenchCase
{
    public function createLinter(): ConfigLinter
    {
        return new JsonDecodeLinter();
    }
}
