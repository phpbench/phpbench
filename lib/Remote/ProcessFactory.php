<?php

namespace PhpBench\Remote;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Process\Process;

final class ProcessFactory implements ProcessFactoryInterface
{
    private readonly LoggerInterface $logger;

    /**
     * @param string[]|null $env
     */
    public function __construct(?LoggerInterface $logger = null, private readonly ?array $env = null)
    {
        $this->logger = $logger ?: new NullLogger();
    }

    public function create(string $commandLine, ?float $timeout = null): Process
    {
        $this->logger->debug(sprintf('Spawning process: %s', $commandLine));

        $process = Process::fromShellCommandline($commandLine)
            ->setTimeout($timeout);

        if (null !== $this->env) {
            $process->setEnv($this->env);
        }

        return $process;
    }
}
