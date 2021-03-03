<?php

namespace PhpBench\Remote;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Process\Process;

class ProcessFactory
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?: new NullLogger();
    }

    public function create(string $commandLine, ?float $timeout = null): Process
    {
        $this->logger->debug(sprintf('Spawning process: %s', $commandLine));

        return Process::fromShellCommandline($commandLine)
            ->setTimeout($timeout);
    }
}
