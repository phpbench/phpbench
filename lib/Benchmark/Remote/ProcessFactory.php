<?php

namespace PhpBench\Benchmark\Remote;

use Symfony\Component\Process\Process;

class ProcessFactory
{
    public function create(string $commandLine, ?float $timeout = null): Process
    {
        return Process::fromShellCommandline($commandLine)
            ->setTimeout($timeout);
    }
}
