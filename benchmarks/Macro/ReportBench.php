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
 * Benchmark for the report generation command.
 *
 * @BeforeClassMethods({"createWorkspace", "generateDump"})
 */
class ReportBench extends BaseBenchCase
{
    /**
     * Generate an XML dump before benchmarking the report
     * generation command.
     */
    public static function generateDump()
    {
        // instantiate the benchmark class (this) so that we can
        // run a command.
        $case = new self();
        $case->runCommand('console.command.run', [
            'path' => self::getFunctionalBenchmarkPath(),
            '--dump-file' => self::getWorkspacePath() . '/dump.xml',
        ]);
    }

    public function benchAggregate()
    {
        $this->runCommand('console.command.report', [
            '--file' => [$this->getWorkspacePath() . '/dump.xml'],
            '--report' => ['aggregate'],
        ]);
    }

    public function benchDefault()
    {
        $this->runCommand('console.command.report', [
            '--file' => [$this->getWorkspacePath() . '/dump.xml'],
            '--report' => ['default'],
        ]);
    }

    public function benchEnv()
    {
        $this->runCommand('console.command.report', [
            '--file' => [$this->getWorkspacePath() . '/dump.xml'],
            '--report' => ['env'],
        ]);
    }
}
