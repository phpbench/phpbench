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

namespace PhpBench\Extensions\XDebug\Tests\System;

class TraceCommandTest extends XDebugTestCase
{
    /**
     * It should run when given a path.
     * It should show the default (simple) report.
     */
    public function testCommand()
    {
        $process = $this->phpbench('xdebug:trace benchmarks/set1/BenchmarkBench.php --filter=benchDoNothing');
        $this->assertExitCode(0, $process);
        $this->assertStringContainsString('Time inc', $process->getOutput());
    }

    /**
     * It should produce an XML dump of the trace.
     */
    public function testDumpTrace()
    {
        $process = $this->phpbench('xdebug:trace benchmarks/set1/BenchmarkBench.php --dump --filter=benchDoNothing');
        $this->assertExitCode(0, $process);
        $this->assertStringContainsString('<trace', $process->getOutput());
    }

    /**
     * It should dump the trace into a specific directory.
     */
    public function testDumpTraceOutDir()
    {
        $process = $this->phpbench('xdebug:trace benchmarks/set1/BenchmarkBench.php --dump --filter=benchDoNothing --outdir=foobar');
        $this->assertExitCode(0, $process);
        $this->assertStringContainsString('<trace', $process->getOutput());
        $this->assertFileExists('foobar');
    }

    /**
     * It should be able to not filter out the benchmarking infrastructure.
     */
    public function testDumpTraceNoBenchmarkFilter()
    {
        $process = $this->phpbench('xdebug:trace benchmarks/set1/BenchmarkBench.php --filter=benchDoNothing --no-benchmark-filter --outdir=foobar');
        $this->assertExitCode(0, $process);
    }

    /**
     * It should show the arguments.
     */
    public function testDumpTraceShowArgs()
    {
        $process = $this->phpbench('xdebug:trace benchmarks/set1/BenchmarkBench.php --filter=benchRandom --show-args --outdir=foobar');
        $this->assertExitCode(0, $process);
        $this->assertStringContainsString('rand', $process->getOutput());
        $this->assertStringContainsString('1000', $process->getOutput());
    }

    /**
     * It should filter the trace.
     */

    /**
     * It should show the arguments.
     */
    public function testDumpTraceFilter()
    {
        $process = $this->phpbench('xdebug:trace benchmarks/set1/BenchmarkBench.php --filter=benchRandom --trace-filter=rand --outdir=foobar');
        $this->assertExitCode(0, $process);
        $this->assertStringContainsString('rand', $process->getOutput());
    }
}
