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

use InvalidArgumentException;
use PhpBench\Registry\Config;
use PhpBench\Tests\TestCase;

class ConfigTest extends TestCase
{
    /**
     * It should throw an exception if an offset does not exist.
     *
     */
    public function testExceptionOffsetNotExist(): void
    {
        $this->expectExceptionMessage('Configuration offset "offset_not_exist" does not exist.');
        $this->expectException(InvalidArgumentException::class);
        $config = new Config(
            'test',
            [
                'foo' => 'bar',
                'bar' => [
                    'one' => 1,
                    'two' => 2,
                ],
            ]
        );
        $config['offset_not_exist']; // @phpstan-ignore-line
    }

    /**
     * It should throw an exception if an invalid name is given.
     *
     * @dataProvider provideInvalidName
     */
    public function testInvalidName(string $name): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Config($name, []);
    }

    /**
     * @return list<list{string}>
     */
    public static function provideInvalidName(): array
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
    public function testGoodName(string $name): void
    {
        $config = new Config($name, []);
        $this->assertEquals($name, $config->getName());
    }

    /**
     * @return list<list{string}>
     */
    public static function provideGoodName(): array
    {
        return [
            ['helo'],
            ['foo-bar'],
            ['foo_bar'],
        ];
    }
}
