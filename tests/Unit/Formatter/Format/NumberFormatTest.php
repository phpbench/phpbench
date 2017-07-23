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

use PhpBench\Formatter\Format\NumberFormat;
use PHPUnit\Framework\TestCase;

class NumberFormatTest extends TestCase
{
    private $format;

    public function setUp()
    {
        $this->format = new NumberFormat();
    }

    /**
     * It should format a number.
     */
    public function testNumberFormat()
    {
        $result = $this->format->format(1000000, $this->format->getDefaultOptions());
        $this->assertEquals('1,000,000', $result);
    }

    /**
     * It should throw an exception if passed a non-numeric value.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Non-numeric value
     */
    public function testNonNumeric()
    {
        $this->format->format('hello', $this->format->getDefaultOptions());
    }
}
