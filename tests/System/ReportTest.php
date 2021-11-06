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

namespace PhpBench\Tests\System;

class ReportTest extends SystemTestCase
{
    /**
     * It should generate a report.
     */
    public function testGenerateReport(): void
    {
        $this->getBenchResult();
        $process = $this->phpbench(
            'report --file=' . $this->fname . ' --report=default'
        );
        $this->assertEquals(0, $process->getExitCode());
        $output = $process->getOutput();
        $this->assertStringContainsString('benchNothing', $output);
    }

    /**
     * It should generate a report when given the --ref option.
     */
    public function testGenerateReportFromUuid(): void
    {
        $document = $this->getBenchResult(null, ' --store');
        $ref = $document->evaluate('string(./suite/@uuid)');
        $process = $this->phpbench(
            'report --ref=' . $ref . ' --report=default'
        );
        $this->assertEquals(0, $process->getExitCode());
        $output = $process->getOutput();
        $this->assertStringContainsString('benchNothing', $output);
    }

    public function testGenerateFilteredReport(): void
    {
        $document = $this->getBenchResult(null, ' --store');
        $ref = $document->evaluate('string(./suite/@uuid)');
        $process = $this->phpbench(
            'report --ref=' . $ref . ' --report=default --filter=Anything --variant=nothing'
        );
        $this->assertEquals(0, $process->getExitCode());
        $output = $process->getOutput();
        $this->assertEmpty($output);
    }

    /**
     * It should allow the mode, precision and time-unit to be specified.
     */
    public function testTimeUnitOverride(): void
    {
        $this->getBenchResult();
        $process = $this->phpbench(
            'report --file=' . $this->fname . ' --report=default --time-unit=seconds --mode=throughput --precision=6'
        );
        $output = $process->getOutput();
        $this->assertExitCode(0, $process);
        $this->assertStringContainsString('100,000.000000ops/s', $output);
    }

    /**
     * It should throw an exception if no reports are specified.
     */
    public function testNoReports(): void
    {
        $process = $this->phpbench('report --file=results/report1.xml');
        $this->assertExitCode(1, $process);
        $this->assertStringContainsString('You must specify or con', $process->getErrorOutput());
    }

    /**
     * It should throw an exception if the report file does not exist.
     */
    public function testNonExistingFile(): void
    {
        $process = $this->phpbench('report tests/Systemist.xml');
        $this->assertExitCode(1, $process);
    }
}
