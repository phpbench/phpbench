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

namespace PhpBench\Tests\Unit\Model\Result;

use PhpBench\Model\Result\MemoryResult;
use PHPUnit\Framework\TestCase;

class MemoryResultTest extends TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Peak memory must be an integer
     */
    public function testPeakMemoryNonInteger()
    {
        new MemoryResult('hello', 10, 10);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Real memory must be an integer
     */
    public function testRealMemoryNonInteger()
    {
        new MemoryResult(10, 'hello', 10);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Final memory must be an integer
     */
    public function testFinalMemoryNonInteger()
    {
        new MemoryResult(10, 10, 'hello');
    }
}
