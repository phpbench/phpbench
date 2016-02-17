<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Report\Generator;

use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Config;
use PhpBench\Report\Generator\TableGenerator;
use PhpBench\Tests\Util\TestUtil;

class TableGeneratorTest extends GeneratorTestCase
{
    /**
     * @var TableGenerator
     */
    private $generator;

    public function setUp()
    {
        $this->generator = new TableGenerator();
    }

    /**
     * It should build an aggregate table.
     */
    public function testAggregate()
    {
        $collection = TestUtil::createCollection(array(
            array(
                'benchmarks' => array('oneBench', 'twoBench'),
                'subjects' => array('subjectOne', 'subjectTwo'),
                'output_time_unit' => 'milliseconds',
                'output_time_precision' => 7,
                'output_mode' => 'throughput',
                'groups' => array('one', 'two', 'three'),
                'break' => array(),
            ),
        ));

        $report = $this->generate($collection);
        $this->assertXPathCount($report, 1, '//table');
        $this->assertXPathCount($report, 4, '//row');
        $this->assertXPathCount($report, 4, '//row[formatter-param[@name="output_time_unit"] = "milliseconds"]');
        $this->assertXPathCount($report, 4, '//row[formatter-param[@name="output_mode"] = "throughput"]');
        $this->assertXPathCount($report, 4, '//row[formatter-param[@name="output_time_precision"] = 7]');
        $this->assertXPathCount($report, 4, '//row[formatter-param[@name="output_time_precision"] = 7]');
        $this->assertXPathCount($report, 4, '//cell[@name="best"]');
        $this->assertXPathCount($report, 4, '//cell[@name="worst"]');

        $this->assertXPathCount($report, 2, '//cell[text() = "oneBench"]');
        $this->assertXPathCount($report, 2, '//cell[text() = "subjectOne"]');
        $this->assertXPathCount($report, 2, '//cell[text() = "subjectTwo"]');
        $this->assertXPathEval($report, 'one,two,three', 'string(//cell[@name="groups"])');
        $this->assertXPathEval($report, '{"param1":"value1"}', 'string(//cell[@name="params"])');
        $this->assertXPathEval($report, 5, 'string(//cell[@name="revs"])');
        $this->assertXPathEval($report, 2, 'string(//cell[@name="its"])');
        $this->assertXPathEval($report, '200', 'string(//cell[@name="mem"])');
        $this->assertXPathEval($report, '2.000', 'string(//cell[@name="best"])');
        $this->assertXPathEval($report, '3.000', 'string(//cell[@name="mean"])');
        $this->assertXPathEval($report, '3.200', 'string(//row[2]//cell[@name="mean"])');
        $this->assertXPathEval($report, '4.000', 'string(//cell[@name="worst"])');
        $this->assertXPathEval($report, '1.000', 'string(//cell[@name="stdev"])');
        $this->assertXPathEval($report, '33.333333333333', 'string(//cell[@name="rstdev"])');

        $this->assertXPathEval($report, '0', 'string(//cell[@name="diff"])');
        $this->assertXPathEval($report, '6.25', 'string(//row[2]//cell[@name="diff"])');
    }

    /**
     * It should allow selection of env columns.
     */
    public function testEnvCols()
    {
        $collection = TestUtil::createCollection(array(
            array(
                'env' => array(
                    'uname' => array(
                        'os' => 'linux',
                        'type' => 'penguin',
                    ),
                ),
            ),
        ));

        $report = $this->generate($collection, array(
            'cols' => array('uname_type', 'uname_os'),
        ));
        $this->assertXPathCount($report, 1, '//cell[@name="uname_type" = "penguin"]');
        $this->assertXPathCount($report, 1, '//cell[@name="uname_os" = "linux"]');
    }

    /**
     * It should break into multiple tables.
     *
     * @dataProvider provideBreak
     */
    public function testBreak(array $breaks, array $assertions)
    {
        $prototype = array(
            'benchmarks' => array('oneBench', 'twoBench'),
            'subjects' => array('subjectOne', 'subjectTwo'),
            'output_time_unit' => 'milliseconds',
            'output_time_precision' => 7,
            'output_mode' => 'throughput',
            'env' => array(
                'uname' => array(
                    'os' => 'linux',
                    'type' => 'penguin',
                ),
            ),
        );
        $collection = TestUtil::createCollection(array(
            $prototype,
            $prototype,
        ));

        $report = $this->generate($collection, array(
            'break' => $breaks,
        ));

        foreach ($assertions as $expression => $count) {
            $this->assertXPathCount($report, $count, $expression);
        }
    }

    public function provideBreak()
    {
        return array(
            array(
                array('benchmark'),
                array(
                    '//table' => 2,
                ),
            ),
            array(
                array('benchmark', 'subject'),
                array(
                    '//table' => 4,
                ),
            ),
            array(
                array('benchmark', 'subject', 'uname_os'),
                array(
                    '//table' => 4,
                ),
            ),
        );
    }

    /**
     * It should provide the specfied columns in the specified order.
     */
    public function testColumns()
    {
        $collection = TestUtil::createCollection(array(
            array(),
        ));

        $report = $this->generate($collection, array(
            'cols' => array('mean', 'mode', 'benchmark', 'subject'),
        ));

        $this->assertXPathCount($report, 4, '//cell');
        $this->assertXPathCount($report, 0, '//cell[@name="mean"]/preceding-sibling::cell');
        $this->assertXPathCount($report, 1, '//cell[@name="mode"]/preceding-sibling::cell');
        $this->assertXPathCount($report, 2, '//cell[@name="benchmark"]/preceding-sibling::cell');
        $this->assertXPathCount($report, 3, '//cell[@name="subject"]/preceding-sibling::cell');
    }

    /**
     * It should isolate a factor and compare statistics horizontally.
     */
    public function testCompare()
    {
        $collection = TestUtil::createCollection(array(
            array(
                'benchmarks' => array('oneBench', 'twoBench'),
                'subjects' => array('subjectOne', 'subjectTwo'),
                'env' => array(
                    'git' => array(
                        'branch' => 'foobar',
                    ),
                ),
            ),
            array(
                'benchmarks' => array('oneBench', 'twoBench'),
                'subjects' => array('subjectOne', 'subjectTwo'),
                'env' => array(
                    'git' => array(
                        'branch' => 'barfoo',
                    ),
                ),
            ),
        ));

        $report = $this->generate($collection, array(
            'compare' => 'git_branch',
            'compare_fields' => array('mode', 'mean'),
            'break' => array(),
        ));

        $this->assertXPathCount($report, 11, '//row[1]/cell');
        $this->assertXPathCount($report, 1, '//table');
        $this->assertXPathCount($report, 4, '//cell[@name="git_branch:foobar:mode"]');
        $this->assertXPathCount($report, 4, '//cell[@name="git_branch:foobar:mean"]');
        $this->assertXPathCount($report, 4, '//cell[@name="git_branch:barfoo:mode"]');
        $this->assertXPathCount($report, 4, '//cell[@name="git_branch:barfoo:mean"]');
    }

    /**
     * Compare should expand duplicate values, e.g. if "mean" appears twice or more for the criteria which we
     * are comparing, then additional columns should be added, mean#1, mean#2 .. mean#n.
     */
    public function testCompareExpandDuplicate()
    {
        $collection = TestUtil::createCollection(array(
            array(
                'benchmarks' => array('oneBench'),
                'subjects' => array('subjectOne'),
            ),
            array(
                'benchmarks' => array('oneBench'),
                'subjects' => array('subjectOne'),
            ),
            array(
                'benchmarks' => array('oneBench'),
                'subjects' => array('subjectOne'),
            ),
        ));

        $report = $this->generate($collection, array(
            'compare' => 'revs',
            'compare_fields' => array('mean'),
            'break' => array(),
        ));

        $this->assertXPathCount($report, 9, '//row[1]/cell');
        $this->assertXPathCount($report, 1, '//cell[@name="revs:5:mean"]');
        $this->assertXPathCount($report, 1, '//cell[@name="revs:5:mean#1"]');
        $this->assertXPathCount($report, 1, '//cell[@name="revs:5:mean#2"]');
    }

    /**
     * It should add table and report titles and descriptions.
     */
    public function testTitles()
    {
        $collection = TestUtil::createCollection(array(
            array(),
        ));

        $report = $this->generate($collection, array(
            'title' => 'Hello World',
            'description' => 'The world said hello back.',
        ));

        $this->assertXPathCount($report, 1, '//report[@title="Hello World"]');
        $this->assertXPathCount($report, 1, '//report[description="The world said hello back."]');

        // the table title is the break criteria, in this case the suite index.
        $this->assertXPathCount($report, 1, '//table[@title="suite: 0"]');
    }

    /**
     * It should sort ASC.
     */
    public function testSortAsc()
    {
        $collection = TestUtil::createCollection(array(
            array(
                'benchmarks' => array('oneBench', 'twoBench'),
                'subjects' => array('subjectOne', 'subjectTwo'),
            ),
        ));
        $report = $this->generate($collection, array(
            'sort' => array('mean' => 'asc'),
        ));

        $values = array();
        foreach ($report->query('//group[@name="body"]//cell[@name="mean"]') as $cellEl) {
            $values[] = $cellEl->nodeValue;
        }
        $this->assertEquals(array(
            '3', '3', '3.2', '3.2',
        ), $values);
    }

    /**
     * It should sort DESC.
     */
    public function testSortDesc()
    {
        $collection = TestUtil::createCollection(array(
            array(
                'benchmarks' => array('oneBench', 'twoBench'),
                'subjects' => array('subjectOne', 'subjectTwo'),
            ),
        ));
        $report = $this->generate($collection, array(
            'sort' => array('mean' => 'desc'),
        ));

        $values = array();
        foreach ($report->query('//group[@name="body"]//cell[@name="mean"]') as $cellEl) {
            $values[] = $cellEl->nodeValue;
        }
        $this->assertEquals(array(
            '3.2', '3.2', '3', '3',
        ), $values);
    }

    /**
     * It should sort multiple columns.
     */
    public function testSortMultiple()
    {
        $collection = TestUtil::createCollection(array(
            array(
                'benchmarks' => array('oneBench', 'twoBench'),
                'subjects' => array('subjectOne', 'subjectTwo'),
                'iterations' => array(8),
            ),
            array(
                'benchmarks' => array('oneBench', 'twoBench'),
                'subjects' => array('subjectOne'),
                'iterations' => array(3),
            ),
        ));
        $report = $this->generate($collection, array(
            'sort' => array('subject' => 'asc', 'mean' => 'desc'),
            'break' => array(),
        ));

        $subjects = array();
        foreach ($report->query('//group[@name="body"]//row') as $cellEl) {
            $subjects[] = $cellEl->evaluate('string(cell[@name="subject"])');
            $values[] = $cellEl->evaluate('string(./cell[@name="mean"])');
        }
        $this->assertEquals(array(
            'subjectOne', 'subjectOne', 'subjectOne', 'subjectOne', 'subjectTwo', 'subjectTwo',
        ), $subjects);
        $this->assertEquals(array(
            '3.6', '3.6', '2.6', '2.6', '3.8', '3.8',
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

    private function generate(SuiteCollection $collection, array $config = array())
    {
        $config = new Config('test', array_merge(
            $this->generator->getDefaultConfig(),
            $config
        ));

        return $this->generator->generate($collection, $config);
    }
}
