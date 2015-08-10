<?php

namespace PhpBench\Tests\Unit\Benchmark;

use PhpBench\Benchmark\Telespector;

class TelespectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should generate a script from a given template, execute it
     * and return the results.
     */
    public function testExecute()
    {
        $teleporter = new Telespector(__DIR__ . '/../../../vendor/autoload.php', '.');
        $result = $teleporter->execute(__DIR__ . '/template/foo.template', array(
            'foo' => 'bar'
        ));

        $this->assertEquals(array(
            'foo' => 'bar'
        ), $result);
    }

    /**
     * It should throw an exception if the script is invalid
     *
     * @expectedException RuntimeException
     * @expectedExceptionMessage Could not execute script
     */
    public function testInvalidScript()
    {
        $teleporter = new Telespector(__DIR__ . '/../../../vendor/autoload.php', '.');
        $teleporter->execute(__DIR__ . '/template/invalid.template', array(
            'foo' => 'bar'
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
        $teleporter = new Telespector('really_does_not_exist.com', null);
        $teleporter->execute(__DIR__ . '/template/foo.template', array());
    }
}
