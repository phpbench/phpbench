<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Environment;

use PhpBench\Environment\Information;

class InformationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Information acts as an array.
     */
    public function testActsAsArray()
    {
        $information = new Information(
            'hello',
            array(
                'one' => 'two',
                'three' => 'four',
            )
        );

        $this->assertEquals('two', $information['one']);
        $this->assertEquals('four', $information['three']);
    }

    /**
     * It should throw an Exception if unset is called.
     *
     * @expectedException BadMethodCallException
     */
    public function testUnset()
    {
        $information = new Information('hello', array());
        unset($information['foo']);
    }

    /**
     * It should throw an Exception if set is called.
     *
     * @expectedException BadMethodCallException
     */
    public function testSet()
    {
        $information = new Information('hello', array());
        $information['foo'] = 'bar';
    }

    /**
     * It should retrieve its name.
     */
    public function testGetName()
    {
        $information = new Information('foo', array());
        $this->assertEquals('foo', $information->getName());
    }

    /**
     * It should be iterable.
     */
    public function testIterable()
    {
        $information = new Information('foo', array(
            'bar' => 'bar',
            'boo' => 'boo',
        ));

        $result = array();
        foreach ($information as $key => $value) {
            $result[$key] = $value;
        }

        $this->assertEquals(array(
            'bar' => 'bar',
            'boo' => 'boo',
        ), $result);
    }
}
