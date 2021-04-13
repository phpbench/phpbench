<?php

namespace PhpBench\Tests;

class PhpBenchTestCase extends IntegrationTestCase
{
    protected function workspacePath(string $path = null): string
    {
        return $this->workspace()->path($path);
    }

    protected function initWorkspace(): void
    {
        $this->workspace()->reset();
    }
}
