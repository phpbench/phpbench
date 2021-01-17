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

namespace PhpBench\Tests\Unit\Environment;

use BadMethodCallException;
use PhpBench\Environment\Information;
use PhpBench\Tests\TestCase;

class InformationTest extends TestCase
{
    /**
     * Information acts as an array.
     */
    public function testActsAsArray(): void
    {
        $information = new Information(
            'hello',
            [
                'one' => 'two',
                'three' => 'four',
            ]
        );

        $this->assertEquals('two', $information['one']);
        $this->assertEquals('four', $information['three']);
    }

    /**
     * It should throw an Exception if unset is called.
     *
     */
    public function testUnset(): void
    {
        $this->expectException(BadMethodCallException::class);
        $information = new Information('hello', []);
        unset($information['foo']);
    }

    /**
     * It should throw an Exception if set is called.
     *
     */
    public function testSet(): void
    {
        $this->expectException(BadMethodCallException::class);
        $information = new Information('hello', []);
        $information['foo'] = 'bar';
    }

    /**
     * It should retrieve its name.
     */
    public function testGetName(): void
    {
        $information = new Information('foo', []);
        $this->assertEquals('foo', $information->getName());
    }

    /**
     * It should be iterable.
     */
    public function testIterable(): void
    {
        $information = new Information('foo', [
            'bar' => 'bar',
            'boo' => 'boo',
        ]);

        $result = [];

        foreach ($information as $key => $value) {
            $result[$key] = $value;
        }

        $this->assertEquals([
            'bar' => 'bar',
            'boo' => 'boo',
        ], $result);
    }

    public function testFlattensArrays(): void
    {
        $information = new Information('foo', [
            'a' => 'b',
            'c' => [
                'd' => [
                    'e' => 'f',
                ],
                'g' => 'h',
            ],
            'i' => 'j',
        ]);

        $this->assertEquals([
            'a' => 'b',
            'c_d_e' => 'f',
            'c_g' => 'h',
            'i' => 'j',
        ], $information->toArray());
    }
}
