<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Functional\Report\Generator;

use PhpBench\Benchmark\SuiteDocument;

class TabularGeneratorTest extends GeneratorTestCase
{
    protected function getGenerator()
    {
        $generator = $this->getContainer()->get('report_generator.tabular');

        return $generator;
    }

    /**
     * It should generate an iteration report by default.
     */
    public function testDefault()
    {
        $dom = $this->generate(
            $this->getSuiteDocument(),
            array()
        );

        $this->assertInstanceOf('PhpBench\Dom\Document', $dom);
        $this->assertEquals(2, $dom->xpath()->evaluate('count(//cell[text() = "Foobar"])'));
    }

    /**
     * It should filter based on group.
     */
    public function testGroupFilter()
    {
        $dom = $this->generate(
            $this->getSuiteDocument(),
            array(
                'groups' => array('notexisting'),
            )
        );

        $this->assertInstanceOf('PhpBench\Dom\Document', $dom);
        $this->assertEquals(0, $dom->xpath()->evaluate('count(//cell[text() = "Foobar"])'));

        $dom = $this->generate(
            $this->getSuiteDocument(),
            array(
                'groups' => array('two'),
            )
        );

        $this->assertEquals(2, $dom->xpath()->evaluate('count(//cell[text() = "Foobar"])'));
    }

    /**
     * It should generate an aggregate report.
     */
    public function testAggregate()
    {
        $dom = $this->generate(
            $this->getSuiteDocument(),
            array(
                'aggregate' => true,
            )
        );

        $this->assertInstanceOf('PhpBench\Dom\Document', $dom);
        $this->assertEquals(1, $dom->xpath()->evaluate('count(//cell[text() = "Foobar"])'));
        $this->assertEquals(1, $dom->xpath()->evaluate('count(//cell[text() = "mySubject"])'));
    }

    /**
     * It should exclude columns.
     */
    public function testExclude()
    {
        $dom = $this->generate(
            $this->getSuiteDocument(),
            array(
                'exclude' => array('time_net', 'benchmark'),
            )
        );

        $this->assertInstanceOf('PhpBench\Dom\Document', $dom);
        $this->assertEquals(0, $dom->xpath()->evaluate('count(//group[@name="body"]//cell[@name="time_net"])'));
        $this->assertEquals(2, $dom->xpath()->evaluate('count(//group[@name="body"]//cell[@name="time"])'));
        $this->assertEquals(0, $dom->xpath()->evaluate('count(//group[@name="body"]//cell[@name="benchmark"])'));
    }

    /**
     * It should show the title.
     */
    public function testTitle()
    {
        $dom = $this->generate(
            $this->getSuiteDocument(),
            array(
                'title' => 'Hello World',
            )
        );

        $this->assertEquals(1.0, $dom->xpath()->evaluate('count(//report[@title = "Hello World"])'));
    }

    /**
     * It should show the description.
     */
    public function testDescription()
    {
        $dom = $this->generate(
            $this->getSuiteDocument(),
            array(
                'description' => 'Hello World',
            )
        );

        $this->assertEquals(1.0, $dom->xpath()->evaluate('count(//description[text() = "Hello World"])'));
    }

    /**
     * It should sort ASC.
     */
    public function testSortAsc()
    {
        $dom = $this->generate(
            $this->getSuiteDocument(),
            array(
                'sort' => array('time' => 'asc'),
            )
        );

        $values = array();
        foreach ($dom->xpath()->query('//group[@name="body"]//cell[@name="time"]') as $cellEl) {
            $values[] = $cellEl->nodeValue;
        }
        $this->assertEquals(array(
            '75.0000μs', '100.0000μs',
        ), $values);
    }

    /**
     * It should sort DESC.
     */
    public function testSortDesc()
    {
        $dom = $this->generate(
            $this->getSuiteDocument(),
            array(
                'sort' => array('time' => 'desc'),
            )
        );

        $values = array();
        foreach ($dom->xpath()->query('//group[@name="body"]//cell[@name="time"]') as $cellEl) {
            $values[] = $cellEl->nodeValue;
        }
        $this->assertEquals(array(
            '100.0000μs', '75.0000μs',
        ), $values);
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
            <group name="two" />
            <group name="three" />
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
