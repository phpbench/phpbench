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

namespace PhpBench\Remote\Exception;

/**
 * Thrown in the case of an error in the remote script.
 */
class ScriptErrorException extends \RuntimeException
{
    /**
     * @var int
     */
    private $exitCode;

    public function __construct(string $message, int $exitCode = null)
    {
        $this->message = $message;
        $this->exitCode = $exitCode;
    }

    public function getExitCode(): ?int
    {
        return $this->exitCode;
    }
}
