<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Model;

use Throwable;

/**
 * Represents an Error. Typically this is
 * a serializable representation of an Exception.
 */
class Error
{
    private $message;
    private $code;
    private $line;
    private $file;
    private $class;
    private $trace;

    public function __construct(
        $message,
        $class,
        $code,
        $file,
        $line,
        $trace
    ) {
        $this->message = $message;
        $this->class = $class;
        $this->line = $line;
        $this->file = $file;
        $this->code = $code;
        $this->trace = $trace;
    }

    public static function fromException(Throwable $exception)
    {
        return new self(
            $exception->getMessage(),
            get_class($exception),
            $exception->getCode(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getLine()
    {
        return $this->line;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getTrace()
    {
        return $this->trace;
    }
}
