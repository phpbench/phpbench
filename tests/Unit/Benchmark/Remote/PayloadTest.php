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
use Symfony\Component\Process\ProcessBuilder;
use PhpBench\Benchmark\Remote\Payload;
use Prophecy\Argument;

class PayloadTest extends \PHPUnit_Framework_TestCase
{
    private $process;

    public function setUp()
    {
        $this->process = $this->prophesize('Symfony\Component\Process\Process');
    }

    /**
     * It should generate a script from a given template, launch it
     * and return the results.
     */
    public function testLaunch()
    {
        $payload = new Payload(
            __DIR__ . '/template/foo.template',
            array(
                'foo' => 'bar',
            )
        );

        $result = $payload->launch($payload);

        $this->assertEquals(array(
            'foo' => 'bar',
        ), $result);
    }

    /**
     * It should throw an exception if the script is invalid.
     *un
     * @expectedException RuntimeException
     * @expectedExceptionMessage Could not launch script
     */
    public function testInvalidScript()
    {
        $payload = new Payload(
            __DIR__ . '/template/invalid.template'
        );

        $payload->launch($payload);
    }

    /**
     * It should customize the PHP binary path
     */
    public function testBinaryPath()
    {
        $process = $this->prophesize('Symfony\Component\Process\Process');
        $payload = new Payload(
            __DIR__ . '/template/foo.template',
            array(),
            $process->reveal()
        );
        $payload->setPhpPath('/foo/bar');
        $process->setCommandLine(Argument::containingString('/foo/bar'))->shouldBeCalled();
        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);
        $process->getOutput()->willReturn('{"foo": "bar"}');

        $payload->launch($payload);
    }

    /**
     * It should pass PHP ini settings to the PHP executable
     */
    public function testPhpConfig()
    {
        $process = $this->prophesize('Symfony\Component\Process\Process');
        $payload = new Payload(
            __DIR__ . '/template/foo.template',
            array(),
            $process->reveal()
        );
        $payload->setPhpConfig(array(
            'foo' => 'bar',
            'bar' => 'foo',
        ));
        $process->setCommandLine(Argument::containingString('-dfoo=bar'))->shouldBeCalled();
        $process->setCommandLine(Argument::containingString('-dbar=foo'))->shouldBeCalled();
        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);
        $process->getOutput()->willReturn('{"foo": "bar"}');

        $payload->launch($payload);
    }

    /**
     * It should allow the PHP executable to be wrapped with a different executable
     */
    public function testWrap()
    {
        $process = $this->prophesize('Symfony\Component\Process\Process');
        $payload = new Payload(
            __DIR__ . '/template/foo.template',
            array(),
            $process->reveal()
        );
        $payload->setWrapper('bockfire');
        $payload->setPhpPath('/boo/bar/php');
        $process->setCommandLine(Argument::containingString('bockfire /boo/bar/php'))->shouldBeCalled();
        $process->run()->shouldBeCalled();
        $process->isSuccessful()->willReturn(true);
        $process->getOutput()->willReturn('{"foo": "bar"}');

        $payload->launch($payload);
    }
}
