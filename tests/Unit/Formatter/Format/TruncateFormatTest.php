<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Formatter\Format;

use PhpBench\Formatter\Format\TruncateFormat;

class TruncateFormatTest extends \PHPUnit_Framework_TestCase
{
    private $format;

    public function setUp()
    {
        $this->format = new TruncateFormat();
    }

    /**
     * It has a truncate function that truncates strings.
     *
     * @dataProvider provideTruncate
     */
    public function testTruncate($expected, $value, $length, $position = 'left', $pad = '...')
    {
        $result = $this->format->format($value, array(
            'length' => $length,
            'position' => $position,
            'pad' => $pad,
        ));
        $this->assertEquals($expected, $result);
    }

    public function provideTruncate()
    {
        return array(
            array(
                'this is not too long',
                'this is not too long',
                225,
            ),
            array(
                '...ng',
                'this is too long',
                5,
            ),
            array(
                'th...',
                'this is too long',
                5,
                'right',
            ),
            array(
                'this.',
                'this is too long',
                5,
                'right',
                '.',
            ),
            array(
                't...g',
                'this is too long',
                5,
                'middle',
                '...',
            ),
            array(
                't...ng',
                'this is too long',
                6,
                'middle',
                '...',
            ),
            array(
                'th..ng',
                'this is too long',
                6,
                'middle',
                '..',
            ),
            array(
                '..g',
                'this is too long',
                3,
                'middle',
                '..',
            ),
        );
    }
}
