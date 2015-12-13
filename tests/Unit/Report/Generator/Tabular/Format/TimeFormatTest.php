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

class TimeFormatTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should work with the default options.
     */
    public function testTimeFormatDefault()
    {
        $format = new TimeFormat();
        $result = $format->format('1234', $format->getDefaultOptions());
        $this->assertEquals(1234, $result);
    }

    /**
     * It should convert the time.
     */
    public function testTimeFormatConvert()
    {
        $format = new TimeFormat();
        $result = $format->format(2000, array(
            'from' => 'milliseconds',
            'to' => 'seconds',
        ));
        $this->assertEquals(2, $result);
    }
}
