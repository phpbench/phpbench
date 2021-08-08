<?php

namespace PhpBench\Examples\Benchmark\ConfigLinters;

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
