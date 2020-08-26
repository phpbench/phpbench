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

namespace PhpBench\Tests\Unit\Report\Generator;

use PhpBench\Dom\Document;
use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Config;
use PhpBench\Report\Generator\TableGenerator;
use PhpBench\Tests\Util\TestUtil;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TableGeneratorTest extends GeneratorTestCase
{
    /**
     * @var TableGenerator
     */
    private $generator;

    protected function setUp(): void
    {
        $this->generator = new TableGenerator();
    }

    /**
     * It should build an aggregate table.
     */
    public function testAggregate(): void
    {
        $collection = TestUtil::createCollection([
            [
                'benchmarks' => ['oneBench', 'twoBench'],
                'subjects' => ['subjectOne', 'subjectTwo'],
                'output_time_unit' => 'milliseconds',
                'output_time_precision' => 7,
                'output_mode' => 'throughput',
                'groups' => ['one', 'two', 'three'],
                'break' => [],
            ],
        ]);

        $report = $this->generate($collection);
        $this->assertXPathCount($report, 1, '//table');
        $this->assertXPathCount($report, 4, '//row');
        $this->assertXPathCount($report, 4, '//row[formatter-param[@name="output_time_unit"] = "milliseconds"]');
        $this->assertXPathCount($report, 4, '//row[formatter-param[@name="output_mode"] = "throughput"]');
        $this->assertXPathCount($report, 4, '//row[formatter-param[@name="output_time_precision"] = 7]');
        $this->assertXPathCount($report, 4, '//row[formatter-param[@name="output_time_precision"] = 7]');
        $this->assertXPathCount($report, 4, '//cell[@name="best"]');
        $this->assertXPathCount($report, 4, '//cell[@name="worst"]');

        $this->assertXPathCount($report, 2, '//cell/value[text() = "oneBench"]');
        $this->assertXPathCount($report, 2, '//cell/value[text() = "subjectOne"]');
        $this->assertXPathCount($report, 2, '//cell/value[text() = "subjectTwo"]');
        $this->assertXPathEval($report, 'one,two,three', 'string(//cell[@name="groups"])');
        $this->assertXPathEval($report, '{"param1":"value1"}', 'string(//cell[@name="params"])');
        $this->assertXPathEval($report, 5, 'string(//cell[@name="revs"])');
        $this->assertXPathEval($report, 2, 'string(//cell[@name="its"])');
        $this->assertXPathEval($report, 200, 'string(//cell[@name="mem_peak"])');
        $this->assertXPathEval($report, 2, 'string(//cell[@name="best"])');
        $this->assertXPathEval($report, 3, 'string(//cell[@name="mean"])');
        $this->assertXPathEval($report, 3.2, 'string(//row[2]//cell[@name="mean"])');
        $this->assertXPathEval($report, 4, 'string(//cell[@name="worst"])');
        $this->assertXPathEval($report, 1, 'string(//cell[@name="stdev"])');
        $this->assertXPathEval($report, 33.333333333333, 'string(//cell[@name="rstdev"])');

        $this->assertXPathEval($report, '1', 'string(//cell[@name="diff"])');
        $this->assertXPathEval($report, '1.0666666666667', 'string(//row[2]//cell[@name="diff"])');
    }

    public function testBaseline(): void
    {
        $suite = TestUtil::createSuite([
            'benchmarks' => ['oneBench'],
            'subjects' => ['subjectOne'],

            'revs' => 1,
            'iterations' => [ 20, 20 ],
            'basetime' => 0,
        ]);
        $baselineSuite = TestUtil::createSuite([
            'benchmarks' => ['oneBench'],
            'subjects' => ['subjectOne'],
            'revs' => 1,
            'iterations' => [ 10, 10 ],
            'basetime' => 0,
        ]);

        $suite->findVariant('oneBench', 'subjectOne', '0')->attachBaseline($baselineSuite->findVariant('oneBench', 'subjectOne', '0'));

        $report = $this->generate(new SuiteCollection([$suite]));

        $this->assertXPathCount($report, 1, '//table');
        $this->assertXPathCount($report, 1, '//row');
        $this->assertXPathCount($report, 4, '//value[@role="baseline_percentage_diff"]');
        $this->assertXPathEval($report, '100', 'string(//cell[@name="mode"]/value[@role="baseline_percentage_diff"])');
    }


    /**
     * It should not crash if an itreation reports 0 time.
     */
    public function testZeroTime(): void
    {
        $collection = TestUtil::createCollection([
            [
                'basetime' => 0,
                'iterations' => [0],
            ],
        ]);

        $report = $this->generate($collection);
        $this->assertXPathEval($report, '0', 'string(//cell[@name="diff"])');
        $this->assertXPathEval($report, '0', 'string(//cell[@name="mean"])');
    }

    /**
     * It should generate iteration rows.
     */
    public function testIterationRows(): void
    {
        $collection = TestUtil::createCollection([
            [
                'benchmarks' => ['oneBench', 'twoBench'],
                'subjects' => ['subjectOne', 'subjectTwo'],
                'output_time_unit' => 'milliseconds',
                'output_time_precision' => 7,
                'output_mode' => 'throughput',
                'groups' => ['one', 'two', 'three'],
                'break' => [],
            ],
        ]);

        $report = $this->generate($collection, [
            'iterations' => true,
            'cols' => ['time_rev', 'comp_z_value', 'iter', 'revs'],
        ]);
        $report->formatOutput = true;
        $this->assertXPathCount($report, 1, '//table');
        $this->assertXPathCount($report, 8, '//row');
        $this->assertXPathEval($report, 2, 'number(//row[1]//cell[@name="time_rev"])');
        $this->assertXPathEval($report, -1, 'number(//row[1]//cell[@name="comp_z_value"])');
        $this->assertXPathEval($report, 0, 'number(//row[1]//cell[@name="iter"])');

        $this->assertXPathEval($report, 4, 'number(//row[2]//cell[@name="time_rev"])');
        $this->assertXPathEval($report, 1, 'number(//row[2]//cell[@name="comp_z_value"])');
        $this->assertXPathEval($report, 1, 'number(//row[2]//cell[@name="iter"])');
    }

    /**
     * It should allow selection of env columns.
     */
    public function testEnvCols(): void
    {
        $collection = TestUtil::createCollection([
            [
                'env' => [
                    'uname' => [
                        'os' => 'linux',
                        'type' => 'penguin',
                    ],
                ],
            ],
        ]);

        $report = $this->generate($collection, [
            'cols' => ['uname_type', 'uname_os'],
        ]);
        $this->assertXPathCount($report, 1, '//cell[@name="uname_type" = "penguin"]');
        $this->assertXPathCount($report, 1, '//cell[@name="uname_os" = "linux"]');
    }

    /**
     * It should break into multiple tables.
     *
     * @dataProvider provideBreak
     */
    public function testBreak(array $breaks, array $assertions): void
    {
        $prototype = [
            'benchmarks' => ['oneBench', 'twoBench'],
            'subjects' => ['subjectOne', 'subjectTwo'],
            'output_time_unit' => 'milliseconds',
            'output_time_precision' => 7,
            'output_mode' => 'throughput',
            'env' => [
                'uname' => [
                    'os' => 'linux',
                    'type' => 'penguin',
                ],
            ],
        ];
        $collection = TestUtil::createCollection([
            $prototype,
            $prototype,
        ]);

        $report = $this->generate($collection, [
            'break' => $breaks,
        ]);

        foreach ($assertions as $expression => $count) {
            $this->assertXPathCount($report, $count, $expression);
        }
    }

    public function provideBreak()
    {
        return [
            [
                ['benchmark'],
                [
                    '//table' => 2,
                ],
            ],
            [
                ['benchmark', 'subject'],
                [
                    '//table' => 4,
                ],
            ],
            [
                ['benchmark', 'subject', 'uname_os'],
                [
                    '//table' => 4,
                ],
            ],
        ];
    }

    /**
     * It should provide the specfied columns in the specified order.
     */
    public function testColumns(): void
    {
        $collection = TestUtil::createCollection([
            [],
        ]);

        $report = $this->generate($collection, [
            'cols' => ['mean', 'mode', 'benchmark', 'subject'],
        ]);

        $this->assertXPathCount($report, 4, '//cell');
        $this->assertXPathCount($report, 0, '//cell[@name="mean"]/preceding-sibling::cell');
        $this->assertXPathCount($report, 1, '//cell[@name="mode"]/preceding-sibling::cell');
        $this->assertXPathCount($report, 2, '//cell[@name="benchmark"]/preceding-sibling::cell');
        $this->assertXPathCount($report, 3, '//cell[@name="subject"]/preceding-sibling::cell');
    }

    /**
     * It should isolate a factor and compare statistics horizontally.
     *
     * @dataProvider provideCompare
     */
    public function testCompare($config, $assertions): void
    {
        $collection = TestUtil::createCollection([
            [
                'benchmarks' => ['oneBench', 'twoBench'],
                'subjects' => ['subjectOne', 'subjectTwo'],
                'env' => [
                    'git' => [
                        'branch' => 'foobar',
                    ],
                ],
            ],
            [
                'benchmarks' => ['oneBench', 'twoBench'],
                'subjects' => ['subjectOne', 'subjectTwo'],
                'env' => [
                    'git' => [
                        'branch' => 'barfoo',
                    ],
                ],
            ],
        ]);

        $report = $this->generate($collection, $config);

        foreach ($assertions as $expectedCount => $xpath) {
            $this->assertXPathCount($report, $expectedCount, $xpath);
        }
    }

    public function provideCompare()
    {
        return [
            [
                [
                    'compare' => 'git_branch',
                    'compare_fields' => ['mode', 'mean'],
                    'break' => [],
                ],
                [
                    12 => '//row[1]/cell',
                    1 => '//table',
                    4 => '//cell[@name="git_branch:foobar:mem_peak"]',
                    4 => '//cell[@name="git_branch:foobar:mean"]',
                    4 => '//cell[@name="git_branch:barfoo:mem_peak"]',
                    4 => '//cell[@name="git_branch:barfoo:mean"]',
                ],
            ],
            [
                [
                    'compare' => 'git_branch',
                    'compare_fields' => ['mem_peak'],
                    'break' => ['benchmark'],
                    'cols' => ['subject'],
                ],
                [
                    6 => '//row[1]/cell',
                    2 => '//table',
                    0 => '//cell[@name="mem"]',
                    4 => '//cell[@name="git_branch:foobar:mem_peak"]',
                    4 => '//cell[@name="git_branch:barfoo:mem_peak"]',
                ],
            ],
        ];
    }

    /**
     * Compare should expand duplicate values, e.g. if "mean" appears twice or more for the criteria which we
     * are comparing, then additional columns should be added, mean#1, mean#2 .. mean#n.
     */
    public function testCompareExpandDuplicate(): void
    {
        $collection = TestUtil::createCollection([
            [
                'benchmarks' => ['oneBench'],
                'subjects' => ['subjectOne'],
            ],
            [
                'benchmarks' => ['oneBench'],
                'subjects' => ['subjectOne'],
            ],
            [
                'benchmarks' => ['oneBench'],
                'subjects' => ['subjectOne'],
            ],
        ]);

        $report = $this->generate($collection, [
            'compare' => 'revs',
            'compare_fields' => ['mean'],
            'break' => [],
        ]);

        $this->assertXPathCount($report, 10, '//row[1]/cell');
        $this->assertXPathCount($report, 1, '//cell[@name="revs:5:mean"]');
        $this->assertXPathCount($report, 1, '//cell[@name="revs:5:mean#1"]');
        $this->assertXPathCount($report, 1, '//cell[@name="revs:5:mean#2"]');
    }

    /**
     * It should add table and report titles and descriptions.
     */
    public function testTitles(): void
    {
        $collection = TestUtil::createCollection([
            [
            ],
        ]);

        $report = $this->generate($collection, [
            'title' => 'Hello World',
            'description' => 'The world said hello back.',
        ]);

        $this->assertXPathCount($report, 1, '//report[@title="Hello World"]');
        $this->assertXPathCount($report, 1, '//report[description="The world said hello back."]');

        // the table title is the break criteria, in this case the suite index.
        $this->assertXPathCount($report, 1, '//table[@title="tag: test, suite: 0, date: 2016-02-06, stime: 00:00:00"]');
    }

    /**
     * It should sort ASC.
     */
    public function testSortAsc(): void
    {
        $collection = TestUtil::createCollection([
            [
                'benchmarks' => ['oneBench', 'twoBench'],
                'subjects' => ['subjectOne', 'subjectTwo'],
            ],
        ]);
        $report = $this->generate($collection, [
            'sort' => ['mean' => 'asc'],
        ]);

        $values = [];

        foreach ($report->query('//group[@name="body"]//cell[@name="mean"]') as $cellEl) {
            $values[] = $cellEl->nodeValue;
        }
        $this->assertEquals([
            '3', '3', '3.2', '3.2',
        ], $values);
    }

    /**
     * It should sort DESC.
     */
    public function testSortDesc(): void
    {
        $collection = TestUtil::createCollection([
            [
                'benchmarks' => ['oneBench', 'twoBench'],
                'subjects' => ['subjectOne', 'subjectTwo'],
            ],
        ]);
        $report = $this->generate($collection, [
            'sort' => ['mean' => 'desc'],
        ]);

        $values = [];

        foreach ($report->query('//group[@name="body"]//cell[@name="mean"]') as $cellEl) {
            $values[] = $cellEl->nodeValue;
        }
        $this->assertEquals([
            '3.2', '3.2', '3', '3',
        ], $values);
    }

    /**
     * It should sort multiple columns.
     */
    public function testSortMultiple(): void
    {
        $collection = TestUtil::createCollection([
            [
                'benchmarks' => ['oneBench', 'twoBench'],
                'subjects' => ['subjectOne', 'subjectTwo'],
                'iterations' => [8],
            ],
            [
                'benchmarks' => ['oneBench', 'twoBench'],
                'subjects' => ['subjectOne'],
                'iterations' => [3],
            ],
        ]);
        $report = $this->generate($collection, [
            'sort' => ['subject' => 'asc', 'mean' => 'desc'],
            'break' => [],
        ]);

        $subjects = [];

        foreach ($report->query('//group[@name="body"]//row') as $cellEl) {
            $subjects[] = $cellEl->evaluate('string(cell[@name="subject"])');
            $values[] = $cellEl->evaluate('string(./cell[@name="mean"])');
        }
        $this->assertEquals([
            'subjectOne', 'subjectOne', 'subjectOne', 'subjectOne', 'subjectTwo', 'subjectTwo',
        ], $subjects);
        $this->assertEquals([
            '3.6', '3.6', '2.6', '2.6', '3.8', '3.8',
        ], $values);
    }

    /**
     * It should pretty print parameters.
     */
    public function testPrettyParams(): void
    {
        $dom = $this->generate(
            TestUtil::createCollection([
                [
                    'parameters' => [
                        'foo' => 'bar',
                        'array' => ['one', 'two'],
                        'assoc_array' => [
                            'one' => 'two',
                            'three' => 'four',
                        ],
                    ],
                ],
            ]),
            [
                'pretty_params' => true,
            ]
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
     * It should normalize the column names for each row.
     */
    public function testNormalizeColumnNames(): void
    {
        $report = $this->generate(
            TestUtil::createCollection([
                [
                    'env' => [
                        'uname' => [
                            'os' => 'linux',
                        ],
                    ],
                ],
                [
                    'env' => [
                        'foobar' => [
                            'os' => 'linux',
                        ],
                    ],
                ],
            ]),
            [
                'cols' => ['uname_os', 'foobar_os'],
            ]
        );

        $this->assertXPathCount($report, 2, '//cell[@name="uname_os"]');
        $this->assertXPathCount($report, 1, '//cell[@name="uname_os"]/value[text() = "linux"]');
        $this->assertXPathCount($report, 2, '//cell[@name="foobar_os"]');
    }

    /**
     * It should customize the column names.
     */
    public function testCustomizeColumnLabels(): void
    {
        $report = $this->generate(
            TestUtil::createCollection([[]]),
            [
                'labels' => [
                    'benchmark' => 'Column one',
                    'subject' => 'Column two',
                    'params' => 'Parameters',
                ],
            ]
        );

        $report->formatOutput = true;
        $this->assertXPathCount($report, 14, '//col');
        $this->assertXPathEval($report, 'Column one', 'string(//table/cols/col[1]/@label)');
        $this->assertXPathEval($report, 'Column two', 'string(//table/cols/col[2]/@label)');
        $this->assertXPathEval($report, 'groups', 'string(//table/cols/col[3]/@label)');
        $this->assertXPathEval($report, 'Parameters', 'string(//table/cols/col[4]/@label)');
        $this->assertXPathEval($report, 'revs', 'string(//table/cols/col[5]/@label)');
    }

    private function generate(SuiteCollection $collection, array $config = []): Document
    {
        $options = new OptionsResolver();
        $this->generator->configure($options);

        $config = new Config('test', $options->resolve($config));

        return $this->generator->generate($collection, $config);
    }
}
