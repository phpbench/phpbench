<?php

namespace PhpBench\Tests\Unit\Report\Generator;

use Symfony\Component\Console\Output\OutputInterface;
use PhpBench\Report\Generator\ConsoleTableGenerator;
use PhpBench\Result\IterationResult;
use PhpBench\Result\IterationsResult;
use PhpBench\Result\SubjectResult;
use PhpBench\Result\BenchmarkResult;
use PhpBench\Result\SuiteResult;
use PhpBench\OptionsResolver\OptionsResolver;
use Symfony\Component\Console\Output\BufferedOutput;

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
     */
    public function testGenerate()
    {
        $config = array(
            'headers' => array(
                'Revs',
                'Time',
            ),
            'cells' => array(
                'revs' => 'string(.//@revs)',
                'time' => 'string(.//@time)',
            ),
        );

        $this->generate($config);
        $expected = <<<EOF
+------+------+
| Revs | Time |
+------+------+
| 1    | 100  |
| 1    | 75   |
+------+------+
EOF
        ;
        $this->assertEquals(trim($expected), trim($this->output->fetch()));
    }

    /**
     * It should only iterate over the given selector
     */
    public function testGenerateWithSelector()
    {
        $config = array(
            'headers' => array(
                'Revs',
                'Time',
            ),
            'selector' => '//subject',
            'cells' => array(
                'revs' => 'string(sum(.//@revs))',
                'time' => 'string(sum(.//@time))',
            ),
        );

        $this->generate($config);
        $expected = <<<EOF
+------+------+
| Revs | Time |
+------+------+
| 2    | 175  |
+------+------+
EOF
        ;
        $this->assertEquals(trim($expected), trim($this->output->fetch()));
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
     */
    public function testGenerateInvalidCellExpression()
    {
        $config = array(
            'headers' => array(
                'Time',
            ),
            'cells' => array(
                'time' => './@time',
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
            'My Subject\'s description',
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
