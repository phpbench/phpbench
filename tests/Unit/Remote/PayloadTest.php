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

use PhpBench\Remote\Payload;
use PhpBench\Remote\ProcessFactory;
use PhpBench\Tests\TestCase;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use RuntimeException;
use Symfony\Component\Process\Process;

class PayloadTest extends TestCase
{
    private $process;
    private $processFactory;

    protected function setUp(): void
    {
        $this->process = $this->createMock(Process::class);
        $this->processFactory = $this->createMock(ProcessFactory::class);
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

        $result = $payload->launch();

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

        $payload->launch();
    }

    /**
     * It should customize the PHP binary path.
     */
    public function testBinaryPath(): void
    {
        $payload = $this->validPayload();
        $payload->setPhpPath('/foo/bar');

        $this->mockProcessFactory(self::once(), '/foo/bar');
        $this->process->expects(self::once())->method('run');
        $this->process->method('isSuccessful')->willReturn(true);
        $this->process->method('getOutput')->willReturn(serialize(['foo' => 'bar']));

        $payload->launch();
    }

    public function mockProcessFactory(InvocationOrder $invocation, string $argument): void
    {
        $this->processFactory
            ->expects($invocation)
            ->method('create')
            ->with($this->stringContains($argument), null)
            ->willReturn($this->process);
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

        $this->mockProcessFactory(self::once(), '-dfoo=bar');
        $this->mockProcessFactory(self::once(), '-dbar=foo');
        $this->process->expects(self::once())->method('run');
        $this->process->expects(self::once())->method('run');
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
        $payload->setWrapper('blackfire');
        $payload->setPhpPath('/boo/bar/php');

        $this->mockProcessFactory(self::once(), 'blackfire \'/boo/bar/php\'');
        $this->process->expects(self::once())->method('run');
        $this->process->method('run');
        $this->process->method('isSuccessful')->willReturn(true);
        $this->process->method('getOutput')->willReturn(serialize(['foo' => 'bar']));

        $payload->launch();
    }

    /**
     * It should throw an exception if a template is not found.
     */
    public function testTemplateNotFound(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Could not find script template');
        $processFactory = $this->createMock(ProcessFactory::class);
        $payload = new Payload(
            __DIR__ . '/template/not-existing-filename.template',
            [],
            null,
            null,
            $processFactory
        );

        $payload->launch();
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
