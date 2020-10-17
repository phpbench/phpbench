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

namespace PhpBench\Tests\Unit\Benchmark\Remote;

use PhpBench\Benchmark\Remote\Payload;
use PhpBench\Benchmark\Remote\PayloadBuilder;
use PhpBench\Benchmark\Remote\ProcessFactory;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use RuntimeException;
use Symfony\Component\Process\Process;

class PayloadTest extends TestCase
{
    private $process;
    private $processFactory;

    protected function setUp(): void
    {
        $this->process = $this->prophesize(Process::class);
        $this->processFactory = $this->prophesize(ProcessFactory::class);
    }

    /**
     * It should generate a script from a given template, launch it
     * and return the results.
     */
    public function testLaunch(): void
    {
        $payload = new Payload(
            __DIR__ . '/template/foo.template',
            [
                'foo' => 'bar',
            ]
        );

        $result = $payload->launch($payload);

        $this->assertEquals([
            'foo' => 'bar',
        ], $result);
    }

    /**
     * It should throw an exception if the script is invalid.
     *
     */
    public function testInvalidScript()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('syntax error');
        $payload = new Payload(
            __DIR__ . '/template/invalid.template'
        );

        $payload->launch();
    }

    /**
     * It should pass PHP ini settings to the PHP executable.
     */
    public function testPhpConfig()
    {
        $payload = $this->validPayload();

        $payload->includePhpConfig([
            'foo' => 'bar',
        ]);
        $payload->includePhpConfig([
            'bar' => 'foo',
        ]);

        $this->processFactory->create(Argument::containingString('-dfoo=bar'), null)->willReturn($this->process)->shouldBeCalled();
        $this->processFactory->create(Argument::containingString('-dbar=foo'), null)->willReturn($this->process)->shouldBeCalled();
        $this->process->run()->shouldBeCalled();
        $this->process->run()->shouldBeCalled();
        $this->process->isSuccessful()->willReturn(true);
        $this->process->getOutput()->willReturn(serialize(['foo' => 'bar']));

        $payload->launch();
    }

    /**
     * It should allow the PHP executable to be wrapped with a different executable.
     */
    public function testWrap()
    {
        $payload = $this->validPayload();
        $payload->withPhpWrapper('bockfire');
        $payload->withPhpBinary('/boo/bar/php');
        $this->processFactory->create(Argument::containingString('bockfire \'/boo/bar/php\''), null)->willReturn($this->process)->shouldBeCalled();
        $this->process->run()->shouldBeCalled();
        $this->process->isSuccessful()->willReturn(true);
        $this->process->getOutput()->willReturn(serialize(['foo' => 'bar']));

        $payload->launch();
    }

    /**
     * It should throw an execption if a template is not found.
     */
    public function testTemplateNotFound()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not find script template');
        $processFactory = $this->prophesize(ProcessFactory::class);
        $payload = (new PayloadBuilder(
            __DIR__ . '/template/not-existing-filename.template',
            [],
            $processFactory->reveal()
        ));

        $payload->launch($payload);
    }

    private function validPayload(): PayloadBuilder
    {
        return (new PayloadBuilder(
            __DIR__ . '/template/foo.template',
            [],
            $this->processFactory->reveal()
        ));
    }
}
