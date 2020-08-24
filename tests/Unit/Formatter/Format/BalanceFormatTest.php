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

namespace PhpBench\Tests\Unit\Formatter\Format;

use PhpBench\Formatter\Format\BalanceFormat;
use PHPUnit\Framework\TestCase;

class BalanceFormatTest extends TestCase
{
    /**
     * @var BalanceFormat
     */
    private $format;

    protected function setUp(): void
    {
        $this->format = new BalanceFormat();
    }

    /**
     * It should format positive numbers
     * It should format negative numbers
     * It should format neutral numbers.
     */
    public function testFormat(): void
    {
        $result = $this->format->format(0, $this->format->getDefaultOptions());
        $this->assertEquals('+0', $result);

        $result = $this->format->format(-1, $this->format->getDefaultOptions());
        $this->assertEquals('-1', $result);

        $result = $this->format->format(1, $this->format->getDefaultOptions());
        $this->assertEquals('+1', $result);
    }
}
