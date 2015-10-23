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
        $result = $launcher->payload(
            __DIR__ . '/template/foo.template',
            array(
                'foo' => 'bar',
            )
        )->launch();

        $this->assertEquals(array(
            'foo' => 'bar',
        ), $result);
    }

    /**
     * It should throw an exception if the bootstrap file does not exist.
     *
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Bootstrap file
     */
    public function testInvalidBootstrap()
    {
        $launcher = new Launcher(__DIR__ . '/../../../../vendor/notexisting.php', '.');
        $launcher->payload(
            __DIR__ . '/template/foo.template',
            array(
                'foo' => 'bar',
            )
        );
    }

    /**
     * It should return the bootstrap path relative to the base path.
     *
     * @dataProvider provideBootstrapRelativity
     */
    public function testBootstrapRelativity($bootstrap, $expected)
    {
        $launcher = new Launcher($bootstrap, __DIR__ . '/launcher');
        $payload = $launcher->payload(
            __DIR__ . '/template/foo.template',
            array(
                'foo' => 'bar',
            )
        );

        $refl = new \ReflectionClass($payload);
        $tokens = $refl->getProperty('tokens');
        $tokens->setAccessible(true);
        $tokens = $tokens->getValue($payload);
        $this->assertEquals($expected, $tokens['bootstrap']);
    }

    public function provideBootstrapRelativity()
    {
        return array(
            array(
                'autoload.php',
                __DIR__ . '/launcher/autoload.php',
            ),
            array(
                __DIR__ . '/launcher/autoload.php',
                __DIR__ . '/launcher/autoload.php',
            ),
        );
    }
}
