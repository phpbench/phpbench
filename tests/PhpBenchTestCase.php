<?php

namespace PhpBench\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class PhpBenchTestCase extends TestCase
{
    protected function workspacePath(string $path = null): string
    {
        $base = __DIR__ . '/Workspace';

        if (empty($path)) {
            return $base;
        }

        return $base . DIRECTORY_SEPARATOR . $path;
    }

    protected function initWorkspace(): void
    {
        if (file_exists($this->workspacePath())) {
            $filesystem = new Filesystem();
            $filesystem->remove($this->workspacePath());
        }

        mkdir($this->workspacePath(), 0777);
    }
}
