<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Report\Generator\Tabular\Format;

use PhpBench\Report\Generator\Tabular\Format\TimeFormat;
use PhpBench\Util\TimeUnit;

class TimeFormatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should work with the default options.
     */
    public function testTimeFormatDefault()
    {
        $format = new TimeFormat(new TimeUnit());
        $result = $format->format('1234', $format->getDefaultOptions());
        $this->assertEquals('1,234.000μs', $result);
    }

    /**
     * It should convert the time.
     */
    public function testTimeFormatConvert()
    {
        $format = new TimeFormat(new TimeUnit());
        $result = $format->format(2000, array(
            'unit' => 'seconds',
            'mode' => 'throughput',
        ));
        $this->assertEquals('500.000ops/s', $result);
    }
}
