<?php

namespace PhpBench\Tests\Unit\Report\Generator;

use Symfony\Component\Console\Output\OutputInterface;
use PhpBench\Report\Generator\ConsoleTableGenerator;
use PhpBench\Result\IterationResult;
use PhpBench\Result\IterationsResult;
use PhpBench\Result\SubjectResult;
use PhpBench\Result\BenchmarkResult;
use PhpBench\Result\SuiteResult;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConsoleTableGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->output = new BufferedOutput();
        $this->generator = new ConsoleTableGenerator();
        $this->generator->setOutput($this->output);
        $this->optionsResolver = new OptionsResolver();
    }

    /**
     * It should output a basic report
     * It should iterate over a query
     */
    public function testGenerate()
    {
        $config = array(
            'rows' => array(
                array(
                    'cells' => array(
                        'revs' => 'string(.//@revs)',
                        'time' => 'string(.//@time)',
                    ),
                    'with_query' => '//iteration',
                ),
            )
        );

        $this->generate($config);
        $output = $this->output->fetch();
        $this->assertContains('revs', $output);
        $this->assertContains('time', $output);
        $this->assertContains('100μs', $output);
        $this->assertContains('75μs', $output);
    }

    /**
     * It should be able to add a row without iterations
     */
    public function testGenerateSingleRow()
    {
        $config = array(
            'rows' => array(
                array(
                    'cells' => array(
                        'hi' => 'string("hello")',
                        'bye' => 'string("goodbye")',
                    ),
                ),
            )
        );

        $this->generate($config);
        $output = $this->output->fetch();
        $this->assertContains('hello', $output);
        $this->assertContains('goodbye', $output);
    }

    /**
     * It should only iterate over the given selector
     */
    public function testGenerateWithSelector()
    {
        $config = array(
            'rows' => array(
                array(
                    'cells' => array(
                        'revs' => 'string(sum(.//@revs))',
                        'time' => 'string(sum(.//@time))',
                    ),
                    'with_query' => '//subject',
                ),
            )
        );

        $this->generate($config);
        $output = $this->output->fetch();
        $this->assertContains('revs', $output);
        $this->assertContains('time', $output);
        $this->assertContains('2', $output);
        $this->assertContains('175μs', $output);
    }

    /**
     * It should be able to run cell expressions in a second pass
     */
    public function testPostProcess()
    {
        $config = array(
            'rows' => array(
                array(
                    'cells' => array(
                        'revs' => 'string(sum(.//@revs))',
                        'time' => 'string(sum(//cell[@name="revs"]) * 4)',
                    ),
                ),
            ),
            'post_process' => array('time'),
        );

        $this->generate($config);
        $output = $this->output->fetch();
        $this->assertContains('8μs', $output);
    }

    /**
     * It should output XML in debug mode
     */
    public function testDebugMode()
    {
        $config = array(
            'debug' => true,
        );

        $this->generate($config);
        $output = $this->output->fetch();
        $this->assertContains('Suite XML', $output);
        $this->assertContains('Table XML', $output);
        $this->assertContains('phpbench version', $output);
    }

    /**
     * It should output the title and description
     */
    public function testGenerateTitleAndDescription()
    {
        $config = array(
            'title' => 'Hello',
            'description' => 'World',
        );

        $this->generate($config);
        $output = $this->output->fetch();
        $this->assertContains('Hello', $output);
    }

    /**
     * It should throw an exception if a non scalar value is returned by a cell XPath expression
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Expected XPath expression "./@time" to evaluate to a scalar
     */
    public function testGenerateInvalidCellExpression()
    {
        $config = array(
            'rows' => array(
                array(
                    'cells' => array(
                        'time' => './@time',
                    ),
                )
            ),
        );

        $this->generate($config);
    }

    private function generate($config)
    {
        $this->generator->configure($this->optionsResolver);
        $config = $this->optionsResolver->resolve($config);
        $this->generator->generate($this->getSuiteResult(), $config);
    }


    private function getSuiteResult()
    {
        $iteration1 = new IterationResult(array('revs' => 1, 'time' => 100));
        $iteration2 = new IterationResult(array('revs' => 1, 'time' => 75));
        $iterations = new IterationsResult(array($iteration1, $iteration2));
        $subject = new SubjectResult(
            1,
            'mySubject',
            array('one', 'two'),
            array(
                'foo' => 'bar',
                'array' => array('one', 'two'),
                'assoc_array' => array(
                    'one' => 'two',
                    'three' => 'four',
                ),
            ),
            array($iterations)
        );
        $benchmark = new BenchmarkResult('Benchmark\Class', array($subject));
        $suite = new SuiteResult(array($benchmark));

        return $suite;
    }
}
