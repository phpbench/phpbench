<?php

namespace PhpBench\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleLogger extends AbstractLogger
{
    /**
     * @var bool
     */
    private $enable;

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(OutputInterface $output, bool $enable)
    {
        $this->enable = $enable;
        $this->output = $output;
    }

    /**
     * {@inheritDoc}
     */
    public function log($level, $message, array $context = []): void
    {
        $decoration = 'fg=cyan';

        switch ($level) {
            case LogLevel::DEBUG:
            case LogLevel::INFO:
            case LogLevel::NOTICE:
                if (!$this->enable) {
                    return;
                }

                break;
            case LogLevel::WARNING:
                $decoration = 'fg=yellow';

                break;
            case LogLevel::ERROR:
            case LogLevel::CRITICAL:
            case LogLevel::EMERGENCY:
            case LogLevel::ALERT:
                $decoration = 'bg=red;fg=white';

                break;
        }
        $this->output->writeln(sprintf("[<%s>%s</>] %s\n", $decoration, strtoupper($level), $message));
    }
}
