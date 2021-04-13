<?php

namespace PhpBench\Tests\Unit\Logger;

use PhpBench\Logger\ConsoleLogger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleLoggerTest extends TestCase
{
    public function testNoInfoWhenNotDebug(): void
    {
        $output = $this->createOutput();
        $this->createLogger($output, false)->debug('asd');
        $this->createLogger($output, false)->notice('asd');
        $this->createLogger($output, false)->info('asd');
        self::assertEmpty($output->fetch());
    }

    public function testLogInfoWhenDebug(): void
    {
        $output = $this->createOutput();
        $this->createLogger($output, true)->debug('asd');
        self::assertNotEmpty($output->fetch());
        $this->createLogger($output, true)->info('asd');
        self::assertNotEmpty($output->fetch());
        $this->createLogger($output, true)->notice('asd');
        self::assertNotEmpty($output->fetch());
    }

    private function createOutput(): BufferedOutput
    {
        return new BufferedOutput();
    }

    private function createLogger(OutputInterface $output, bool $debug): LoggerInterface
    {
        return new ConsoleLogger($output, $debug);
    }
}
