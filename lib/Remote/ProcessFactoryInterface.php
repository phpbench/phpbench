<?php

declare(strict_types=1);

namespace PhpBench\Remote;

use Symfony\Component\Process\Process;

interface ProcessFactoryInterface
{
    public function create(string $commandLine, ?float $timeout = null): Process;
}
