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
     */
    public function testPeakMemoryNonInteger()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Peak memory must be an integer');
        new MemoryResult('hello', 10, 10);
    }

    /**
     */
    public function testRealMemoryNonInteger()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Real memory must be an integer');
        new MemoryResult(10, 'hello', 10);
    }

    /**
     */
    public function testFinalMemoryNonInteger()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Final memory must be an integer');
        new MemoryResult(10, 10, 'hello');
    }
}
