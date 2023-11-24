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

namespace PhpBench\Tests\Unit\Remote;

use PhpBench\Remote\IniStringBuilder;
use PhpBench\Tests\TestCase;

class IniStringBuilderTest extends TestCase
{
    /**
     * @var IniStringBuilder
     */
    private $builder;

    protected function setUp(): void
    {
        $this->builder = new IniStringBuilder();
    }

    /**
     * @dataProvider provideBuild
     */
    public function testBuild(array $example, string $expectedIniString): void
    {
        $iniString = $this->builder->build($example);
        $this->assertEquals($expectedIniString, $iniString);
    }

    public static function provideBuild()
    {
        return [
            [
                [
                    'a' => 'b',
                ],
                '-da=b',
            ],
            [
                [
                    'a' => 'b',
                    'b' => 'a',
                ],
                '-da=b -db=a',
            ],
            [
                [
                    'a' => ['b', 'c', 'd'],
                ],
                '-da=b -da=c -da=d',
            ],
        ];
    }
}
