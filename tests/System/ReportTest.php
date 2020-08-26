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

use Generator;

class ReportTest extends SystemTestCase
{
    /**
     * It should generate a report.
     */
    public function testGenerateReport()
    {
        $this->getResult();
        $process = $this->phpbench(
            'report --file=' . $this->fname . ' --report=default'
        );
        $this->assertEquals(0, $process->getExitCode());
        $output = $process->getOutput();
        $this->assertStringContainsString('benchNothing', $output);
    }

    /**
     * It should generate a report when given the --uuid option.
     */
    public function testGenerateReportFromUuid()
    {
        $document = $this->getResult(null, ' --store');
        $uuid = $document->evaluate('string(./suite/@uuid)');
        $process = $this->phpbench(
            'report --uuid=' . $uuid . ' --report=default'
        );
        $this->assertEquals(0, $process->getExitCode());
        $output = $process->getOutput();
        $this->assertStringContainsString('benchNothing', $output);
    }

    /**
     * It should allow the mode, precision and time-unit to be specified.
     */
    public function testTimeUnitOverride()
    {
        $this->getResult();
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
    public function testNoReports()
    {
        $process = $this->phpbench('report --file=results/report1.xml');
        $this->assertExitCode(1, $process);
        $this->assertStringContainsString('You must specify or con', $process->getErrorOutput());
    }

    /**
     * It should throw an exception if the report file does not exist.
     */
    public function testNonExistingFile()
    {
        $process = $this->phpbench('report tests/Systemist.xml');
        $this->assertExitCode(1, $process);
    }

    /**
     * It should generate in different output formats.
     *
     * @dataProvider provideOutputs
     */
    public function testOutputs($output)
    {
        $this->getResult();
        $process = $this->phpbench(
            'report --file=' . $this->fname .' --output=' . $output . ' --report=default'
        );

        $this->assertExitCode(0, $process);
    }

    public function provideOutputs()
    {
        return [
            ['html'],
            ['markdown'],
        ];
    }

    /**
     * The core report generators should execute.
     *
     * @dataProvider provideGenerators
     */
    public function testGenerators($config)
    {
        $this->getResult();
        $process = $this->phpbench(
            'report --file=' . $this->fname .' --report=\'' . json_encode($config) . '\''
        );

        $this->assertExitCode(0, $process);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideGenerators(): Generator
    {
        yield [['generator' => 'table']];

        yield [['generator' => 'env']];

        yield [['generator' => 'composite', 'reports' => ['default']]];
    }

    /**
     * The core report generators should execute.
     *
     * @dataProvider providePreconfiguredReports
     */
    public function testPreConfiguredReports(string $reportName): void
    {
        $this->getResult();
        $process = $this->phpbench(
            'report --file=' . $this->fname .' --report=\'' . $reportName . '\''
        );

        $this->assertExitCode(0, $process);
    }

    /**
     * @return Generator<mixed>
     */
    public function providePreconfiguredReports(): Generator
    {
        yield [ 'aggregate' ];
    }
}
