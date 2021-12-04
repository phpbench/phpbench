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

namespace PhpBench\Tests\Unit\Remote;

use PHPUnit\Framework\MockObject\MockObject;
use PhpBench\Remote\Payload;
use PhpBench\Remote\ProcessFactoryInterface;
use PhpBench\Tests\IntegrationTestCase;
use RuntimeException;
use Symfony\Component\Process\Process;

class PayloadTest extends IntegrationTestCase
{
    /**
     * @var MockObject
     */
    private $process;
    /**
     * @var MockObject
     */
    private $processFactory;

    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->process = $this->createMock(Process::class);
        $this->processFactory = $this->createMock(ProcessFactoryInterface::class);
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

    public function testAutomaticallyCreatesNonExistingScriptDirectory(): void
    {
        $payload = new Payload(
            __DIR__ . '/template/foo.template',
            [
                'foo' => 'bar',
            ],
            null,
            null,
            null,
            $this->workspace()->path('/foo/bar/baz')
        );

        $result = $payload->launch($payload);

        $this->assertEquals([
            'foo' => 'bar',
        ], $result);
    }

    /**
     * It should throw an exception if the script is invalid.
     */
    public function testInvalidScript(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('syntax error');
        $payload = new Payload(
            __DIR__ . '/template/invalid.template'
        );

        $payload->launch($payload);
    }

    /**
     * It should customize the PHP binary path.
     */
    public function testBinaryPath(): void
    {
        $payload = $this->validPayload();
        $payload->setPhpPath('/foo/bar');
        $this->processFactory->method('create')->willReturn($this->process);
        $this->process->expects($this->once())->method('run');
        $this->process->method('isSuccessful')->willReturn(true);
        $this->process->method('getOutput')->willReturn(serialize(['foo' => 'bar']));

        $payload->launch();
    }

    /**
     * It should pass PHP ini settings to the PHP executable.
     */
    public function testPhpConfig(): void
    {
        $payload = $this->validPayload();

        $payload->mergePhpConfig([
            'foo' => 'bar',
        ]);
        $payload->mergePhpConfig([
            'bar' => 'foo',
        ]);

        $this->processFactory->expects($this->once())->method('create')->with(
            $this->stringContains('-dfoo=bar -dbar=foo')
        )->willReturn($this->process);
        $this->process->expects($this->once())->method('run');
        $this->process->method('isSuccessful')->willReturn(true);
        $this->process->method('getOutput')->willReturn(serialize(['foo' => 'bar']));

        $payload->launch();
    }

    /**
     * It should allow the PHP executable to be wrapped with a different executable.
     */
    public function testWrap(): void
    {
        $payload = $this->validPayload();
        $payload->setWrapper('bockfire');
        $payload->setPhpPath('/boo/bar/php');
        $this->processFactory->expects($this->once())->method('create')->with(
            $this->stringContains('bockfire \'/boo/bar/php\'')
        )->willReturn($this->process);
        $this->process->expects($this->once())->method('run');
        $this->process->method('isSuccessful')->willReturn(true);
        $this->process->method('getOutput')->willReturn(serialize(['foo' => 'bar']));

        $payload->launch();
    }

    /**
     * It should throw an execption if a template is not found.
     *
     */
    public function testTemplateNotFound(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not find script template');
        $processFactory = $this->createMock(ProcessFactoryInterface::class);
        $payload = new Payload(
            __DIR__ . '/template/not-existing-filename.template',
            [],
            null,
            null,
            $processFactory
        );

        $payload->launch($payload);
    }

    private function validPayload(): Payload
    {
        return $this->validPayloadWithPhpConfig();
    }

    private function validPayloadWithPhpConfig(array $phpConfig = []): Payload
    {
        return new Payload(
            __DIR__ . '/template/foo.template',
            [],
            null,
            null,
            $this->processFactory
        );
    }
}
