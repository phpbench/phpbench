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

namespace PhpBench\Tests\Unit\Registry;

use PhpBench\Registry\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    private $config;

    public function setUp()
    {
    }

    /**
     * It should throw an exception if an offset does not exist.
     *
     * @expectedException InvalidArgumentException
     * @expectedException Configuration offset "offset_not_exist" does not exist.
     */
    public function testExceptionOffsetNotExist()
    {
        $config = new Config(
            'test',
            [
            'foo' => 'bar',
            'bar' => [
                'one' => 1,
                'two' => 2,
            ],
        ]);
        $config['offset_not_exist'];
    }

    /**
     * It should throw an exception if an invalid name is given.
     *
     * @expectedException InvalidArgumentException
     * @dataProvider provideInvalidName
     */
    public function testInvalidName($name)
    {
        new Config($name, []);
    }

    public function provideInvalidName()
    {
        return [
            ['he lo'],
            ['foo&'],
            [':'],
            [''],
        ];
    }

    /**
     * It should allow good names.
     *
     * @dataProvider provideGoodName
     */
    public function testGoodName($name)
    {
        $config = new Config($name, []);
        $this->assertEquals($name, $config->getName());
    }

    public function provideGoodName()
    {
        return [
            ['helo'],
            ['foo-bar'],
            ['foo_bar'],
        ];
    }
}
