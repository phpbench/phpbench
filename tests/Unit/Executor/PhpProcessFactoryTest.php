<?php

namespace PhpBench\Tests\Unit\Executor;

use PHPUnit\Framework\TestCase;
use PhpBench\Executor\PhpProcessFactory;
use PhpBench\Executor\PhpProcessOptions;
use Symfony\Component\Process\Process;

class PhpProcessFactoryTest extends TestCase
{
    public function testDefaultPhpBinPath(): void
    {
        $options = new PhpProcessOptions();
        $process = $this->create($options, 'foo.php');

        self::assertEquals(
            sprintf("'%s' 'foo.php'", PHP_BINARY),
            $process->getCommandLine()
        );
    }

    public function testCustomPhpBinPath(): void
    {
        $options = new PhpProcessOptions();
        $options->phpPath = '/custom/path/to/php';

        $process = $this->create($options, 'foo.php');

        self::assertEquals(
            "'/custom/path/to/php' 'foo.php'",
            $process->getCommandLine()
        );
    }

    public function testWithPhpConfig(): void
    {
        $options = new PhpProcessOptions();
        $options->phpPath = 'php';
        $options->phpConfig = ['foobar' => 'barfoo'];

        $process = $this->create($options, 'foo.php');

        self::assertEquals(
            "'php' '-dfoobar=barfoo' 'foo.php'",
            $process->getCommandLine()
        );
    }

    public function testDisablePhpIni(): void
    {
        $options = new PhpProcessOptions();
        $options->phpPath = 'php';
        $options->disablePhpIni = true;

        $process = $this->create($options, 'foo.php');

        self::assertEquals(
            "'php' '-n' 'foo.php'",
            $process->getCommandLine()
        );
    }

    private function create(PhpProcessOptions $options, string $scriptPath): Process
    {
        return (new PhpProcessFactory($options))->buildProcess($scriptPath);
    }
}
