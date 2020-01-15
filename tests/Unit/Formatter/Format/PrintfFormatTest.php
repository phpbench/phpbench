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

use PhpBench\Formatter\Format\PrintfFormat;
use PHPUnit\Framework\TestCase;

class PrintfFormatTest extends TestCase
{
    protected function setUp(): void
    {
        $this->format = new PrintfFormat();
    }

    /**
     * It should format using sprintf.
     */
    public function testNumberFormat()
    {
        $result = $this->format->format('hai', ['format' => '%s bye']);
        $this->assertEquals('hai bye', $result);
    }
}
