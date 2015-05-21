<?php

namespace PhpBench\Tests\Unit\ReportGenerator;

use PhpBench\ReportGenerator\ConsoleTableReportGenerator;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Console\Output\BufferedOutput;

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
            $this->benchmarkResult->reveal()
        ));
        $this->benchmarkResult->getSubjectResults()->willReturn(array(
            $this->subjectResult->reveal()
        ));
        $this->subjectResult->getIterationsResults()->willReturn(array(
            $this->iterationsResult->reveal()
        ));
        $this->iterationsResult->getIterationResults()->willReturn(array(
            $this->iterationResult1
        ));

        $this->benchmarkResult->getClass()->willReturn('AcmeClass');
        $this->subjectResult->getName()->willReturn('Test');
        $this->subjectResult->getDescription()->willReturn('Nothing');
    }

    /**
     * It should render non scalar parameter values
     */
    public function testNonScalarParameterValue()
    {
        $this->iterationsResult->getParameters()->willReturn(array(
            'hello' => 'goodbye',
            'array' => array('a', 'r', 'r', 'a', 'y'),
            'object' => new \DateTime(),
        ));
        $this->reportGenerator->generate($this->suiteResult->reveal(), $this->output, $this->options);
        $display = $this->output->fetch();
        $this->assertContains('["a","r","r","a","y"]', $display);
        $this->assertContains('obj', $display);
        $this->assertContains('goodbye', $display);
    }
}
