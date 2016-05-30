<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Benchmark\Remote;

use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * Build and execute tokenized scripts in separate processes.
 * The scripts should return a JSON encoded string.
 */
class Launcher
{
    /**
     * @var string
     */
    private $bootstrap;

    /**
     * @var PayloadFactory
     */
    private $factory;

    /**
     * @var string
     */
    private $phpBinary;

    /**
     * @var array
     */
    private $phpConfig;

    /**
     * @var string
     */
    private $phpWrapper;

    /**
     * @param mixed string
     */
    public function __construct(
        PayloadFactory $factory = null,
        ExecutableFinder $finder = null,
        $bootstrap = null,
        $phpBinary = null,
        $phpConfig = [],
        $phpWrapper = null
    ) {
        $this->bootstrap = $bootstrap;
        $this->payloadFactory = $factory ?: new PayloadFactory();
        $this->phpBinary = $phpBinary;
        $this->phpConfig = $phpConfig;
        $this->phpWrapper = $phpWrapper;
        $this->finder = $finder ?: new ExecutableFinder();
    }

    public function payload($template, array $tokens)
    {
        $tokens['bootstrap'] = '';
        if (null !== $this->bootstrap) {
            if (!file_exists($this->bootstrap)) {
                throw new \InvalidArgumentException(sprintf(
                    'Bootstrap file "%s" does not exist.',
                    $this->bootstrap
                ));
            }
            $tokens['bootstrap'] = $this->bootstrap;
        }

        $phpBinary = $this->resolvePhpBinary();

        $payload = $this->payloadFactory->create($template, $tokens, $phpBinary);

        if ($this->phpWrapper) {
            $payload->setWrapper($this->phpWrapper);
        }

        if ($this->phpConfig) {
            $payload->setPhpConfig($this->phpConfig);
        }

        return $payload;
    }

    private function resolvePhpBinary()
    {
        // if no php binary, use the PhpExecutableFinder (generally will
        // resolve to PHP_BINARY)
        if (!$this->phpBinary) {
            $finder = new PhpExecutableFinder();

            return $finder->find();
        }

        // if the php binary is absolute, fine.
        if (substr($this->phpBinary, 0, 1) === '/') {
            return $this->phpBinary;
        }

        // otherwise try and find it in PATH etc.
        $phpBinary = $this->finder->find($this->phpBinary);

        if (null === $phpBinary) {
            throw new \InvalidArgumentException(sprintf(
                'Could not find PHP binary "%s"', $this->phpBinary
            ));
        }

        return $phpBinary;
    }
}
