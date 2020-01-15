<?php

namespace PhpBench\Benchmark\Remote;

use Symfony\Component\Process\Process;

class ProcessFactory
{
    public function create(string $commandLine): Process
    {
        return Process::fromShellCommandline($commandLine)
            ->setTimeout(null);
    }
}
