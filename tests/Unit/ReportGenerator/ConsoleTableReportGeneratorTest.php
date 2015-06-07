<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\ReportGenerator;

use PhpBench\Report\Generator\ConsoleTableReportGenerator;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * TODO: This is difficult to maintain.
 */
class ConsoleTableReportGeneratorTest extends \PHPUnit_Framework_TestCase
{
    private $reportGenerator;
    private $output;

    public function setUp()
    {
        $this->output = new BufferedOutput();
        $this->reportGenerator = new ConsoleTableReportGenerator();
        $optionsResolver = new OptionsResolver();
        $this->reportGenerator->configure($optionsResolver);
        $this->options = $optionsResolver->resolve(array());
        $this->suiteResult = $this->prophesize('PhpBench\Result\SuiteResult');
        $this->benchmarkResult = $this->prophesize('PhpBench\Result\BenchmarkResult');
        $this->subjectResult = $this->prophesize('PhpBench\Result\SubjectResult');
        $this->iterationsResult = $this->prophesize('PhpBench\Result\IterationsResult');
        $this->iterationResult1 = $this->prophesize('PhpBench\Result\IterationResult');

        $this->suiteResult->getBenchmarkResults()->willReturn(array(
            $this->benchmarkResult->reveal(),
        ));
        $this->benchmarkResult->getSubjectResults()->willReturn(array(
            $this->subjectResult->reveal(),
        ));
        $this->subjectResult->getIterationsResults()->willReturn(array(
            $this->iterationsResult->reveal(),
        ));
        $this->subjectResult->getGroups()->willReturn(array(
        ));
        $this->iterationsResult->getIterationResults()->willReturn(array(
            $this->iterationResult1,
        ));

        $this->benchmarkResult->getClass()->willReturn('AcmeClass');
        $this->subjectResult->getName()->willReturn('Test');
        $this->subjectResult->getDescription()->willReturn('Nothing');
    }

    /**
     * It should render non scalar parameter values.
     */
    public function testNonScalarParameterValue()
    {
        $this->iterationsResult->getParameters()->willReturn(array(
            'hello' => 'goodbye',
            'array' => array('a', 'r', 'r', 'a', 'y'),
            'object' => new \DateTime(),
        ));
        $this->iterationResult1->getStatistics()->willReturn(array(
            'index' => 0,
            'time' => 123,
            'revs' => 123,
            'memory' => 123,
            'memory_inc' => 123,
            'memory_diff_inc' => 123,
            'memory_diff' => 123,
        ));
        $this->reportGenerator->generate($this->suiteResult->reveal(), $this->output, $this->options);
        $display = $this->output->fetch();
        $this->assertContains('["a","r","r","a","y"]', $display);
        $this->assertContains('obj', $display);
        $this->assertContains('goodbye', $display);
    }
}
