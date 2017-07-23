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

namespace PhpBench\Tests\Unit\Report\Generator\Tabular\Format;

use PhpBench\Formatter\Format\TimeUnitFormat;
use PhpBench\Util\TimeUnit;
use PHPUnit\Framework\TestCase;

class TimeUnitFormatTest extends TestCase
{
    /**
     * It should work with the default options.
     */
    public function testTimeFormatDefault()
    {
        $format = new TimeUnitFormat(new TimeUnit());
        $result = $format->format('1234', $format->getDefaultOptions());
        $this->assertEquals('1,234.000μs', $result);
    }

    /**
     * It should convert the time.
     */
    public function testTimeFormatConvert()
    {
        $format = new TimeUnitFormat(new TimeUnit());
        $result = $format->format(2000, [
            'unit' => 'seconds',
            'mode' => 'throughput',
            'precision' => 6,
            'resolve' => [],
        ]);
        $this->assertEquals('500.000000ops/s', $result);
    }
}
