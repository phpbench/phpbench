<?php

namespace PhpBench\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleLogger extends AbstractLogger
{
    public function __construct(private readonly OutputInterface $output, private readonly bool $enable)
    {
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param mixed[] $context
     */
    public function log($level, $message, array $context = []): void
    {
        $decoration = 'fg=cyan';

        switch ($level) {
            case LogLevel::DEBUG:
            case LogLevel::INFO:
            case LogLevel::NOTICE:
            case LogLevel::WARNING:
                if (!$this->enable) {
                    return;
                }

                $decoration = 'fg=yellow';

                break;
            case LogLevel::ERROR:
            case LogLevel::CRITICAL:
            case LogLevel::EMERGENCY:
            case LogLevel::ALERT:
                $decoration = 'bg=red;fg=white';

                break;
        }
        $this->output->writeln(sprintf("[<%s>%s</>] %s\n", $decoration, strtoupper((string) $level), $message));
    }
}
