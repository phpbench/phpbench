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

namespace PhpBench\Tests\Unit\Model;

use PhpBench\Model\Result\MemoryResult;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Model\ResultCollection;
use PHPUnit\Framework\TestCase;

class ResultCollectionTest extends TestCase
{
    private $collection;

    protected function setUp(): void
    {
        $this->timeResult = new TimeResult(1);
        $this->memoryResult = new MemoryResult(1, 0, 0);

        $this->collection = new ResultCollection([]);
    }

    /**
     * It can have results added in the constructor.
     */
    public function testAddConstructor()
    {
        $collection = new ResultCollection([
            $expected = new TimeResult(10),
        ]);

        $result = $collection->getResult(TimeResult::class);
        $this->assertSame($expected, $result);
    }

    /**
     * It should be able to have results added to it.
     * It should retrive results.
     */
    public function testAddResult()
    {
        $this->collection->setResult($this->timeResult);
        $this->assertEquals(
            $this->timeResult,
            $this->collection->getResult(TimeResult::class)
        );
    }

    /**
     * It should throw an exception when retrieving a non-existant class.
     *
     */
    public function testNonExistantClass()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Result of class "stdClass" has not been set');
        $this->collection->getResult(\stdClass::class);
    }

    /**
     * It should return a named metric.
     */
    public function testGetNamedMetric()
    {
        $this->collection->setResult($this->timeResult);
        $this->assertEquals(1, $this->collection->getMetric(TimeResult::class, 'net'));
    }

    /**
     * It should throw an exception if the named metric does not exist.
     *
     */
    public function testNamedMetricDoesNotExist()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown metric "foobar" for result class "PhpBench\Model\Result\TimeResult". Available metrics: "net"');
        $this->collection->setResult($this->timeResult);
        $this->assertEquals(1, $this->collection->getMetric(TimeResult::class, 'foobar'));
    }

    /**
     * It should return a default value when using getMetricOrDefault when the
     * class has not been set.
     */
    public function testGetMetricOrDefault()
    {
        $this->collection->setResult($this->timeResult);
        $this->assertEquals(100, $this->collection->getMetricOrDefault('UnknownClass', 'barbar', 100));
    }
}
