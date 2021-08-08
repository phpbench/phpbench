<?php

namespace PhpBench\Examples\Benchmark\Implementations;

use PhpBench\Config\ConfigLinter;

class NoOpLinter implements ConfigLinter
{
    /**
     * {@inheritDoc}
     */
    public function lint(string $path, string $config): void
    {
    }
}
