<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Benchmarks\Macro;

/**
 * This benchmark executes the run command using the benchmark classes
 * from the functional tests (which have "empty" subjects).
 */
class RunBench extends BaseBenchCase
{
    public function benchRun()
    {
        $this->runCommand('console.command.run', [
            'path' => $this->getFunctionalBenchmarkPath(),
        ]);
    }

    public function benchRunAndReport()
    {
        $this->runCommand('console.command.run', [
            'path' => $this->getFunctionalBenchmarkPath(),
            '--report' => ['aggregate'],
        ]);
    }
}
