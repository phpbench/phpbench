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

    /**
     * @var ProcessFactory
     */
    private $processFactory;

    public function __construct(?ProcessFactory $processFactory = null, ?string $remoteScriptPath = null, bool $remoteScriptRemove = false)
    {
        $this->remoteScriptPath = $remoteScriptPath;
        $this->remoteScriptRemove = $remoteScriptRemove;
        $this->processFactory = $processFactory ?: new ProcessFactory();
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
            $this->processFactory,
            $this->remoteScriptPath,
            $this->remoteScriptRemove
        );
    }
}
