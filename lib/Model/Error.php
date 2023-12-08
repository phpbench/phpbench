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
    /**
     * @param string $message
     * @param string $class
     * @param int $code
     * @param string $file
     * @param int $line
     * @param string $trace
     */
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

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return int
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getTrace()
    {
        return $this->trace;
    }
}
