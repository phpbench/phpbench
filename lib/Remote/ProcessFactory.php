<?php

namespace PhpBench\Remote;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Process\Process;

final class ProcessFactory implements ProcessFactoryInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array|null
     */
    private $env;

    public function __construct(?LoggerInterface $logger = null, ?array $env = null)
    {
        $this->logger = $logger ?: new NullLogger();
        $this->env = $env;
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
