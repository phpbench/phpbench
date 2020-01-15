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

namespace PhpBench\Tests\Unit\Benchmark\Remote;

use PhpBench\Benchmark\Remote\IniStringBuilder;
use PHPUnit\Framework\TestCase;

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
    public function testBuild(array $example, string $expectedIniString)
    {
        $iniString = $this->builder->build($example);
        $this->assertEquals($expectedIniString, $iniString);
    }

    public function provideBuild()
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
