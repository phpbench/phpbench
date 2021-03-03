<?php

namespace PhpBench\Logger;

use Psr\Log\AbstractLogger;

class ConsoleLogger extends AbstractLogger
{
    /**
     * @var bool
     */
    private $enable;

    public function __construct(bool $enable)
    {
        $this->enable = $enable;
    }

    /**
     * {@inheritDoc}
     */
    public function log($level, $message, array $context = []): void
    {
        if (false === $this->enable) {
            return;
        }

        fwrite(STDERR, sprintf("[%s] %s\n", $level, $message));
    }
}
