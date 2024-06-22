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
    private readonly ProcessFactory $processFactory;

    public function __construct(?ProcessFactory $processFactory = null, private readonly ?string $remoteScriptPath = null, private readonly bool $remoteScriptRemove = false)
    {
        $this->processFactory = $processFactory ?: new ProcessFactory();
    }

    /**
     * @param array<string, string|null> $tokens
     */
    public function create(string $template, array $tokens = [], ?string $phpBinary = null, ?float $timeout = null): Payload
    {
        return new Payload(
            $template,
            $tokens,
            $phpBinary,
            $timeout,
            $this->processFactory,
            $this->remoteScriptPath,
            $this->remoteScriptRemove
        );
    }
}
