<?php

namespace PhpBench\Tests\Unit\Remote;

use PhpBench\Remote\ProcessFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ProcessFactoryTest extends TestCase
{
    public function testCreatesProcessWithEnv(): void
    {
        $process = (new ProcessFactory(new NullLogger(), [
            'FOO' => 'hello',
        ]))->create('echo $FOO');
        $process->mustRun();
        self::assertStringContainsString('hello', $process->getOutput());
    }

    public function testCreatesProcessInheritsEnv(): void
    {
        $_ENV['FOO'] = 'bar';
        $process = (new ProcessFactory(new NullLogger(), ['BAZ' => 'boo']))->create('echo $FOO$BAZ');
        $process->mustRun();
        self::assertStringContainsString('barboo', $process->getOutput());
    }
}
