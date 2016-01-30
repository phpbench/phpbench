<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Registry;

use PhpBench\Registry\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
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
            array(
            'foo' => 'bar',
            'bar' => array(
                'one' => 1,
                'two' => 2,
            ),
        ));
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
        new Config($name, array());
    }

    public function provideInvalidName()
    {
        return array(
            array('he lo'),
            array('foo&'),
            array(':'),
            array(''),
        );
    }

    /**
     * It should allow good names.
     *
     * @dataProvider provideGoodName
     */
    public function testGoodName($name)
    {
        $config = new Config($name, array());
        $this->assertEquals($name, $config->getName());
    }

    public function provideGoodName()
    {
        return array(
            array('helo'),
            array('foo-bar'),
            array('foo_bar'),
        );
    }
}
