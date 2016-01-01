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
        $this->config = new Config(
            array(
            'foo' => 'bar',
            'bar' => array(
                'one' => 1,
                'two' => 2,
            ),
        ));
    }

    /**
     * It should throw an exception if an offset does not exist.
     *
     * @expectedException InvalidArgumentException
     * @expectedException Configuration offset "offset_not_exist" does not exist.
     */
    public function testExceptionOffsetNotExist()
    {
        $this->config['offset_not_exist'];
    }
}
