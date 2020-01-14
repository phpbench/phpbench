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

use PhpBench\Formatter\Format\TruncateFormat;
use PHPUnit\Framework\TestCase;

class TruncateFormatTest extends TestCase
{
    private $format;

    protected function setUp(): void
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
        $result = $this->format->format($value, [
            'length' => $length,
            'position' => $position,
            'pad' => $pad,
        ]);
        $this->assertEquals($expected, $result);
    }

    public function provideTruncate()
    {
        return [
            [
                'this is not too long',
                'this is not too long',
                225,
            ],
            [
                '...ng',
                'this is too long',
                5,
            ],
            [
                'th...',
                'this is too long',
                5,
                'right',
            ],
            [
                'this.',
                'this is too long',
                5,
                'right',
                '.',
            ],
            [
                't...g',
                'this is too long',
                5,
                'middle',
                '...',
            ],
            [
                't...ng',
                'this is too long',
                6,
                'middle',
                '...',
            ],
            [
                'th..ng',
                'this is too long',
                6,
                'middle',
                '..',
            ],
            [
                '..g',
                'this is too long',
                3,
                'middle',
                '..',
            ],
        ];
    }
}
