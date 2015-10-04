<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Benchmark\Remote;

use PhpBench\Benchmark\Remote\Launcher;

class LauncherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should generate a script from a given template, launch it
     * and return the results.
     */
    public function testExecute()
    {
        $launcher = new Launcher(__DIR__ . '/../../../../vendor/autoload.php', '.');
        $result = $launcher->launch(__DIR__ . '/template/foo.template', array(
            'foo' => 'bar',
        ));

        $this->assertEquals(array(
            'foo' => 'bar',
        ), $result);
    }

    /**
     * It should throw an exception if the script is invalid.
     *
     * @expectedException RuntimeException
     * @expectedExceptionMessage Could not launch script
     */
    public function testInvalidScript()
    {
        $launcher = new Launcher(__DIR__ . '/../../../../vendor/autoload.php', '.');
        $launcher->launch(__DIR__ . '/template/invalid.template', array(
            'foo' => 'bar',
        ));
    }

    /**
     * It should throw an exception if the bootstrap file does not exist.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Bootstrap file
     */
    public function testInvalidBootstrap()
    {
        $launcher = new Launcher('really_does_not_exist.com', null);
        $launcher->launch(__DIR__ . '/template/foo.template', array());
    }
}
