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

namespace PhpBench\Tests\Unit\Formatter\Formatter;

use InvalidArgumentException;
use PhpBench\Formatter\Format\NumberFormat;
use PHPUnit\Framework\TestCase;

class NumberFormatTest extends TestCase
{
    private $format;

    protected function setUp(): void
    {
        $this->format = new NumberFormat();
    }

    /**
     * It should format a number.
     */
    public function testNumberFormat(): void
    {
        $result = $this->format->format(1000000, $this->format->getDefaultOptions());
        $this->assertEquals('1,000,000', $result);
    }

    public function testReturnsINFForInfiniteNumber(): void
    {
        $result = $this->format->format(INF, $this->format->getDefaultOptions());
        $this->assertEquals('INF', $result);
    }

    public function testReturnsINFForStringInfiniteNumber(): void
    {
        $result = $this->format->format('INF', $this->format->getDefaultOptions());
        $this->assertEquals('INF', $result);
    }

    /**
     * It should throw an exception if passed a non-numeric value.
     *
     */
    public function testNonNumeric(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Non-numeric value');
        $this->format->format('hello', $this->format->getDefaultOptions());
    }
}
