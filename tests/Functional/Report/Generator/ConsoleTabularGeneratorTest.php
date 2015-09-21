<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Functional\Report\Generator;

use PhpBench\Benchmark\SuiteDocument;

class ConsoleTabularGeneratorTest extends ConsoleTestCase
{
    protected function getGenerator()
    {
        $generator = $this->getContainer()->get('report_generator.tabular');
        $generator->setOutput($this->getOutput());

        return $generator;
    }

    /**
     * It should generate an iteration report by default.
     */
    public function testDefault()
    {
        $this->generate(
            $this->getSuiteDocument(),
            array()
        );

        $output = $this->getOutput()->fetch();
        $this->assertStringCount(2, 'Foobar', $output);
        $this->assertStringCount(2, 'mySubject', $output);
    }

    /**
     * It should filter based on group.
     */
    public function testGroupFilter()
    {
        $this->generate(
            $this->getSuiteDocument(),
            array(
                'groups' => array('notexisting'),
            )
        );

        $output = $this->getOutput()->fetch();
        $this->assertStringCount(0, 'Foobar', $output);

        $this->generate(
            $this->getSuiteDocument(),
            array(
                'groups' => array('one'),
            )
        );

        $output = $this->getOutput()->fetch();
        $this->assertStringCount(2, 'Foobar', $output);
    }

    /**
     * It should generate an aggregate report.
     */
    public function testAggregate()
    {
        $this->generate(
            $this->getSuiteDocument(),
            array(
                'aggregate' => true,
            )
        );

        $output = $this->getOutput()->fetch();
        $this->assertStringCount(1, 'Foobar', $output);
        $this->assertStringCount(1, 'mySubject', $output);
    }

    /**
     * It should exclude columns.
     */
    public function testExclude()
    {
        $this->generate(
            $this->getSuiteDocument(),
            array(
                'exclude' => array('time_net', 'benchmark'),
            )
        );

        $output = $this->getOutput()->fetch();
        $this->assertStringCount(0, 'Foobar', $output);
        $this->assertStringCount(2, 'mySubject', $output);
        $this->assertStringCount(0, 'time_net', $output);
    }

    /**
     * It should show debug output.
     */
    public function testDebug()
    {
        $this->generate(
            $this->getSuiteDocument(),
            array(
                'debug' => true,
            )
        );

        $output = $this->getOutput()->fetch();
        $this->assertContains('Suite XML', $output);
        $this->assertContains('Table XML', $output);
    }

    /**
     * It should show the title.
     */
    public function testTitle()
    {
        $this->generate(
            $this->getSuiteDocument(),
            array(
                'title' => 'Hello World',
            )
        );

        $output = $this->getOutput()->fetch();
        $this->assertContains('Hello World', $output);
    }

    /**
     * It should show the description.
     */
    public function testDescription()
    {
        $this->generate(
            $this->getSuiteDocument(),
            array(
                'description' => 'Hello World',
            )
        );

        $output = $this->getOutput()->fetch();
        $this->assertContains('Hello World', $output);
    }

    /**
     * It should sort ASC.
     */
    public function testSortAsc()
    {
        $this->generate(
            $this->getSuiteDocument(),
            array(
                'sort' => array('time' => 'asc'),
            )
        );

        $output = $this->getOutput()->fetch();
        preg_match_all('/75\.00|100\.00/m', $output, $matches);
        $this->assertEquals(array(array(
            '75.00', '100.00',
        )), $matches);
    }

    /**
     * It should sort DESC.
     */
    public function testSortDesc()
    {
        $this->generate(
            $this->getSuiteDocument(),
            array(
                'sort' => array('time' => 'desc'),
            )
        );

        $output = $this->getOutput()->fetch();
        preg_match_all('/75\.00|100\.00/m', $output, $matches);
        $this->assertEquals(array(array(
            '100.00', '75.00',
        )), $matches);
    }

    private function getSuiteDocument()
    {
        $suite = new SuiteDocument();
        $suite->loadXml(<<<EOT
<?xml version="1.0"?>
<phpbench version="0.x">
    <benchmark class="Foobar">
        <subject name="mySubject">
            <group name="one" />
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
