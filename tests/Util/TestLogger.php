<?php

namespace PhpBench\Tests\Util;

use Psr\Log\AbstractLogger;

/**
 * Used for testing purposes.
 *
 * It records all records and gives you access to them for verification.
 *
 */
class TestLogger extends AbstractLogger
{
    /** @var mixed[] */
    public array $records = [];

    public function reset(): void
    {
        $this->records = [];
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param mixed[] $context
     */
    public function log($level, $message, array $context = []): void
    {
        $record = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];

        $this->records[] = $record;
    }
}
