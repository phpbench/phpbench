<?php

namespace PhpBench\Executor\Exception;

use RuntimeException;

class ExecutionError extends RuntimeException
{
    public function __construct(string $title, string $body = null)
    {
        $this->message = sprintf("%s\n\n%s", $title, $body);
    }
}
