<?php

namespace PhpBench\Tests\Unit\Benchmark\Remote;

use PHPUnit\Framework\TestCase;
use PhpBench\Benchmark\Remote\IniStringBuilder;

class IniStringBuilderTest extends TestCase
{
    /**
     * @var IniStringBuilder
     */
    private $builder;

    public function setUp()
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
                    'a' => 'b'
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
                    'a' => ['b','c','d'],
                ],
                '-da=b -da=c -da=d',
            ],
        ];
    }
}

