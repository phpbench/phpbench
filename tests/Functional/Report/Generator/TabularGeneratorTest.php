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

use PhpBench\Tests\Util\TestUtil;

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
            TestUtil::createCollection(array(
                array(
                    'groups' => array('one', 'two', 'three'),
                    'parameters' => array(
                        'foo' => 'bar',
                        'array' => array('one', 'two'),
                        'assoc_array' => array(
                            'one' => 'two',
                            'three' => 'four',
                        ),
                    ),
                ),
            )),
            array()
        );

        $this->assertInstanceOf('PhpBench\Dom\Document', $dom);
        $this->assertXPathEvaluation($dom, 2, 'count(//cell[text() = "TestBench"])');
        $this->assertXPathEvaluation($dom, 2, 'count(//cell[text() = "benchOne"])');
        $this->assertXPathEvaluation($dom, 'one,two,three', 'string(//cell[@name="group"])');
        $this->assertXPathEvaluation(
            $dom,
            '{"foo":"bar","array":["one","two"],"assoc_array":{"one":"two","three":"four"}}',
            'string(//cell[@name="params"])'
            );
        $this->assertXPathEvaluation($dom, '5', 'string(//cell[@name="revs"])');
        $this->assertXPathEvaluation($dom, 1, 'count(//cell[@name="iter"][text() = 0])');
        $this->assertXPathEvaluation($dom, 1, 'count(//cell[@name="iter"][text() = 1])');
        $this->assertXPathEvaluation($dom, '0', 'string(//cell[@name="rej"])');
        $this->assertXPathEvaluation($dom, '200b', 'string(//cell[@name="mem"])');
        $this->assertXPathEvaluation($dom, '2.000μs', 'string(//row[position()=1]//cell[@name="time"])');
        $this->assertXPathEvaluation($dom, '4.000μs', 'string(//row[position()=2]//cell[@name="time"])');
        $this->assertXPathEvaluation($dom, '-1σ', 'string(//row[position()=1]//cell[@name="z-score"])');
        $this->assertXPathEvaluation($dom, '+1.00σ', 'string(//row[position()=2]//cell[@name="z-score"])');

        $this->assertXPathEvaluation($dom, '-33.33%', 'string(//row[1]//cell[@name="diff"])');
        $this->assertXPathEvaluation($dom, '+33.33%', 'string(//row[2]//cell[@name="diff"])');
    }

    /**
     * It should filter based on group.
     */
    public function testGroupFilterNonExisting()
    {
        $dom = $this->generate(
            TestUtil::createCollection(array(
                array(),
            )),
            array(
                'groups' => array('notexisting'),
            )
        );

        $this->assertInstanceOf('PhpBench\Dom\Document', $dom);
        $this->assertEquals(0, $dom->xpath()->evaluate('count(//cell[text() = "TestBench"])'));
    }

    /**
     * It should filter based on group.
     */
    public function testGroupFilter()
    {
        $dom = $this->generate(
            TestUtil::createCollection(array(
                array(),
            )),
            array(
                'groups' => array('two'),
            )
        );

        $this->assertEquals(2, $dom->xpath()->evaluate('count(//cell[text() = "TestBench"])'));
    }

    /**
     * It should generate an aggregate report.
     */
    public function testAggregate()
    {
        $dom = $this->generate(
            TestUtil::createCollection(array(
                array(
                    'subjects' => array(
                        'benchOne',
                        'benchTwo',
                    ),
                ),
            )),
            array(
                'type' => 'aggregate',
            )
        );

        $this->assertInstanceOf('PhpBench\Dom\Document', $dom);
        $this->assertXPathEvaluation($dom, 2, 'count(//cell[text() = "TestBench"])');
        $this->assertXPathEvaluation($dom, 1, 'count(//cell[text() = "benchOne"])');
        $this->assertXPathEvaluation($dom, 1, 'count(//cell[text() = "benchTwo"])');
        $this->assertXPathEvaluation($dom, 'one,two,three', 'string(//cell[@name="group"])');
        $this->assertXPathEvaluation($dom, '{"param1":"value1"}', 'string(//cell[@name="params"])');
        $this->assertXPathEvaluation($dom, 5, 'string(//cell[@name="revs"])');
        $this->assertXPathEvaluation($dom, 2, 'string(//cell[@name="its"])');
        $this->assertXPathEvaluation($dom, '200b', 'string(//cell[@name="mem"])');
        $this->assertXPathEvaluation($dom, '2.000μs', 'string(//cell[@name="best"])');
        $this->assertXPathEvaluation($dom, '3.000μs', 'string(//cell[@name="mean"])');
        $this->assertXPathEvaluation($dom, '3.200μs', 'string(//row[2]//cell[@name="mean"])');
        $this->assertXPathEvaluation($dom, '4.000μs', 'string(//cell[@name="worst"])');
        $this->assertXPathEvaluation($dom, '1.000μs', 'string(//cell[@name="stdev"])');
        $this->assertXPathEvaluation($dom, '33.33%', 'string(//cell[@name="rstdev"])');

        $this->assertXPathEvaluation($dom, '0.00%', 'string(//cell[@name="diff"])');
        $this->assertXPathEvaluation($dom, '+6.67%', 'string(//row[2]//cell[@name="diff"])');
    }

    /**
     * It should exclude columns.
     */
    public function testExclude()
    {
        $dom = $this->generate(
            TestUtil::createCollection(array(
                array(),
            )),
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
            TestUtil::createCollection(array(
                array(),
            )),
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
            TestUtil::createCollection(array(
                array(),
            )),
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
            TestUtil::createCollection(array(
                array(),
            )),
            array(
                'sort' => array('time' => 'asc'),
            )
        );

        $values = array();
        foreach ($dom->xpath()->query('//group[@name="body"]//cell[@name="time"]') as $cellEl) {
            $values[] = $cellEl->nodeValue;
        }
        $this->assertEquals(array(
            '2.000μs', '4.000μs',
        ), $values);
    }

    /**
     * It should sort DESC.
     */
    public function testSortDesc()
    {
        $dom = $this->generate(
            TestUtil::createCollection(array(
                array(),
            )),
            array(
                'sort' => array('time' => 'desc'),
            )
        );

        $values = array();
        foreach ($dom->xpath()->query('//group[@name="body"]//cell[@name="time"]') as $cellEl) {
            $values[] = $cellEl->nodeValue;
        }
        $this->assertEquals(array(
          '4.000μs', '2.000μs',
        ), $values);
    }

    /**
     * It should pretty print parameters.
     */
    public function testPrettyParams()
    {
        $dom = $this->generate(
            TestUtil::createCollection(array(
                array(
                    'parameters' => array(
                        'foo' => 'bar',
                        'array' => array('one', 'two'),
                        'assoc_array' => array(
                            'one' => 'two',
                            'three' => 'four',
                        ),
                    ),
                ),
            )),
            array(
                'pretty_params' => true,
            )
        );

        $value = null;
        foreach ($dom->xpath()->query('//group[@name="body"]/row[1]/cell[@name="params"]') as $cellEl) {
            $value = $cellEl->nodeValue;
        }
        $this->assertEquals(<<<'EOT'
{
    "foo": "bar",
    "array": [
        "one",
        "two"
    ],
    "assoc_array": {
        "one": "two",
        "three": "four"
    }
}
EOT
        , $value);
    }

    /**
     * It should generate a comparitive report.
     */
    public function testCompare()
    {
        $dom = $this->generate(
            TestUtil::createCollection(array(
                array(
                    'name' => 'foobar',
                    'subjects' => array('benchOne', 'benchTwo'),
                ),
                array(
                    'name' => 'barfoo',
                    'subjects' => array('benchOne', 'benchTwo'),
                    'basetime' => 20,
                ),
            )),
            array(
                'type' => 'compare',
            )
        );

        $this->assertXPathEvaluation($dom, 2, 'count(//group[@name="body"]/row)');
        $this->assertXPathEvaluation($dom, 2, 'count(//group[@name="body"]/row/cell[@name="t:foobar"])');
        $this->assertXPathEvaluation($dom, 2, 'count(//group[@name="body"]/row/cell[@name="t:barfoo"])');
        $this->assertXPathEvaluation($dom, 'one,two,three', 'string(//group[@name="body"]//cell[@name="group"])');
        $this->assertXPathEvaluation($dom, 1, 'count(//cell[text() = "benchOne"])');
        $this->assertXPathEvaluation($dom, 1, 'count(//cell[text() = "benchTwo"])');
        $this->assertXPathEvaluation($dom, '{"param1":"value1"}', 'string(//cell[@name="params"])');
        $this->assertXPathEvaluation($dom, '3.000μs', 'string(//group[@name="body"]/row[position()=1]/cell[@name="t:foobar"])');
        $this->assertXPathEvaluation($dom, '5.000μs', 'string(//group[@name="body"]/row[position()=1]/cell[@name="t:barfoo"])');
        $this->assertXPathEvaluation($dom, '3.200μs', 'string(//group[@name="body"]/row[position()=2]/cell[@name="t:foobar"])');
        $this->assertXPathEvaluation($dom, '5.200μs', 'string(//group[@name="body"]/row[position()=2]/cell[@name="t:barfoo"])');
    }
}
