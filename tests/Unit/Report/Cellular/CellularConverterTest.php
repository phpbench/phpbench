<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Report\Cellular;

use PhpBench\Result\IterationResult;
use PhpBench\Result\IterationsResult;
use PhpBench\Result\SubjectResult;
use PhpBench\Result\BenchmarkResult;
use PhpBench\Result\SuiteResult;
use PhpBench\Report\Cellular\CellularConverter;

class CellularConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should convert a benchmark suite into a Workspace
     * It should create one table per subject.
     */
    public function testConversion()
    {
        $iteration1 = new IterationResult(array(
            'time' => 1000,
            'revs' => 1000,
            'memory' => 123,
            'memory_diff' => 123,
        ));
        $iterations = new IterationsResult(array($iteration1), array());
        $subject1 = new SubjectResult('mySubject1', 'My Subject\'s description', array('one', 'two'), array($iterations));
        $subject2 = new SubjectResult('mySubject2', 'My Subject\'s description', array('one', 'two'), array($iterations));
        $benchmark1 = new BenchmarkResult('Benchmark\Foo', array($subject1, $subject2));
        $suite = new SuiteResult(array($benchmark1));

        $workspace = CellularConverter::suiteToWorkspace($suite);
        $this->assertCount(2, $workspace->getTables());
        $this->assertEquals('Benchmark\Foo->mySubject1', $workspace->getTable(0)->getTitle());
        $this->assertEquals('My Subject\'s description', $workspace->getTable(0)->getDescription());
        $rows = $workspace->getTable(0)->getRows();
        $this->assertCount(1, $rows);
        $row = reset($rows);
        $this->assertEquals(1000, $row['time']->getValue());
        $this->assertEquals(1000, $row['revs']->getValue());
        $this->assertEquals(123, $row['memory']->getValue());
        $this->assertEquals(123, $row['memory_diff']->getValue());
    }
}
