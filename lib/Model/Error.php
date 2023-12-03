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
    public function __construct(private $message, private $class, private $code, private $file, private $line, private $trace)
    {
    }

    public static function fromException(Throwable $exception): Error
    {
        return new self(
            $exception->getMessage(),
            $exception::class,
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
