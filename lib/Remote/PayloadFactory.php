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

namespace PhpBench\Remote;

class PayloadFactory
{
    /**
     * @var string|null
     */
    private $remoteScriptPath;

    /**
     * @var bool
     */
    private $remoteScriptRemove;

    public function __construct(?string $remoteScriptPath = null, bool $remoteScriptRemove = false)
    {
        $this->remoteScriptPath = $remoteScriptPath;
        $this->remoteScriptRemove = $remoteScriptRemove;
    }

    /**
     * @param array<string, mixed> $tokens
     */
    public function create(string $template, array $tokens = [], ?string $phpBinary = null, ?float $timeout = null): Payload
    {
        return new Payload(
            $template,
            $tokens,
            $phpBinary,
            $timeout,
            null,
            $this->remoteScriptPath,
            $this->remoteScriptRemove
        );
    }
}
