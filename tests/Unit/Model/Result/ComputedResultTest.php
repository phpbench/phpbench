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

use PhpBench\Model\Result\ComputedResult;
use PHPUnit\Framework\TestCase;

class ComputedResultTest extends TestCase
{
    /**
     * It should throw an exception if the z-value is non-numeric.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Z-value was not numeric, got "hello"
     */
    public function testZValueNonNumeric()
    {
        new ComputedResult('hello', 10);
    }

    /**
     * It should throw an exception if the deviation is non-numeric.
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Deviation was not numeric, got "hello"
     */
    public function testDeviationNonNumeric()
    {
        new ComputedResult(10, 'hello');
    }
}
