<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Report\Generator;

use PhpBench\Report\Generator\ConsoleTableGenerator;
use Symfony\Component\Console\Output\BufferedOutput;
use PhpBench\Benchmark\SuiteDocument;

class ConsoleTableGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->output = new BufferedOutput();
        $this->generator = new ConsoleTableGenerator();
        $this->generator->setOutput($this->output);
    }

    /**
     * It should output a basic report
     * It should iterate over a query.
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
            ),
        );

        $this->generate($config);
        $output = $this->output->fetch();
        $this->assertContains('revs', $output);
        $this->assertContains('time', $output);
        $this->assertContains('100μs', $output);
        $this->assertContains('75μs', $output);
    }

    /**
     * It should be able to add a row without iterations.
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
            ),
        );

        $this->generate($config);
        $output = $this->output->fetch();
        $this->assertContains('hello', $output);
        $this->assertContains('goodbye', $output);
    }

    /**
     * It should be able to add multiple individual rows.
     */
    public function testGenerateMultipleRows()
    {
        $config = array(
            'rows' => array(
                array(
                    'cells' => array(
                        'hi' => 'string("hello")',
                        'bye' => 'string("goodbye")',
                    ),
                ),
                array(
                    'cells' => array(
                        'salut' => 'string("bonjour")',
                        'ciao' => 'string("aurevoir")',
                    ),
                ),
            ),
        );

        $this->generate($config);
        $output = $this->output->fetch();
        $this->assertContains('hello', $output);
        $this->assertContains('goodbye', $output);
        $this->assertContains('salut', $output);
        $this->assertContains('ciao', $output);
    }

    /**
     * It should allow rows to be iterated with items.
     */
    public function testIterateRowsWithParameters()
    {
        $config = array(
            'rows' => array(
                array(
                    'cells' => array(
                        'hi' => 'string("{{ row.item }}")',
                        'ciao' => 'string("{{ row.item }}")',
                    ),
                    'with_items' => array('hello', 'bye'),
                ),
            ),
        );

        $this->generate($config);
        $output = $this->output->fetch();
        $this->assertContains('hello', $output);
        $this->assertContains('bye', $output);
        $this->assertContains('hi', $output);
        $this->assertContains('ciao', $output);
    }

    /**
     * It should be able to iterate cells with items.
     */
    public function testIterateCellsWithParameters()
    {
        $config = array(
            'rows' => array(
                array(
                    'cells' => array(
                        'hi' => 'string("Hello")',
                        'ciao_{{ cell.item }}' => array(
                            'expr' => 'string("{{ cell.item }}")',
                            'with_items' => array('one', 'two', 'three'),
                        ),
                    ),
                ),
            ),
        );

        $this->generate($config);
        $output = $this->output->fetch();
        $this->assertContains('ciao_one', $output);
        $this->assertContains('ciao_two', $output);
        $this->assertContains('ciao_three', $output);
    }

    /**
     * It should only iterate over the given selector.
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
            ),
        );

        $this->generate($config);
        $output = $this->output->fetch();
        $this->assertContains('revs', $output);
        $this->assertContains('time', $output);
        $this->assertContains('2', $output);
        $this->assertContains('175μs', $output);
    }

    /**
     * It should be able to run cell expressions in a second pass.
     */
    public function testPostProcess()
    {
        $config = array(
            'rows' => array(
                array(
                    'cells' => array(
                        'revs' => 'string(sum(.//@revs))',
                        'time' => array(
                            'expr' => 'string(sum(//cell[@name="revs"]) * 4)',
                            'post_process' => true,
                        ),
                    ),
                ),
            ),
        );

        $this->generate($config);
        $output = $this->output->fetch();
        $this->assertContains('8μs', $output);
    }

    /**
     * It should output XML in debug mode.
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
     * It should output the title and description.
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
     * It should throw an exception if a non scalar value is returned by a cell XPath expression.
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
                ),
            ),
        );

        $this->generate($config);
    }

    /**
     * It should "render" the table even if it has no rows.
     */
    public function testRenderNoOutput()
    {
        $config = array(
            'rows' => array(
            ),
        );

        $this->generate($config);
    }

    private function generate($config)
    {
        $defaults = $this->generator->getDefaultConfig();
        $config = array_merge($defaults, $config);
        $this->generator->generate($this->getSuiteResult(), $config);
    }

    private function getSuiteResult()
    {
        $suite = new SuiteDocument();

        $suite->loadXml(<<<EOT
<?xml version="1.0"?>
<phpbench version="0.x">
    <benchmark class="Foobar">
        <subject name="mySubject">
            <variant>
                <parameter name="foo" value="bar" />
                <parameter name="array" type="collection">
                    <parameter name="0" value="one" />
                    <parameter name="1" value="two" />
                </parameter>
                <parameter name="assoc_array" type="collection">
                    <parameter name="one" value="two" />
                    <parameter name="three" value="four" />
                </parameter>
                <iteration time="100" memory="100" revs="1" />
                <iteration time="75" memory="100" revs="1" />
           </variant>
        </subject>
    </benchmark>
</phpbench>
EOT
        );

        return $suite;
    }
}
