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
    private $payloadFactory;

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
     * @var PayloadFactory
     */
    private $factory;

    /**
     * @var ExecutableFinder
     */
    private $finder;

    /**
     * @var bool
     */
    private $phpDisableIni;

    public function __construct(
        PayloadFactory $factory = null,
        ExecutableFinder $finder = null,
        ?string $bootstrap = null,
        ?string $phpBinary = null,
        array $phpConfig = [],
        ?string $phpWrapper = null,
        bool $phpDisableIni = false
    ) {
        $this->bootstrap = $bootstrap;
        $this->payloadFactory = $factory ?: new PayloadFactory();
        $this->phpBinary = $phpBinary;
        $this->phpConfig = $phpConfig;
        $this->phpWrapper = $phpWrapper;
        $this->finder = $finder ?: new ExecutableFinder();
        $this->factory = $factory;
        $this->phpDisableIni = $phpDisableIni;
    }

    public function payload($template, array $tokens = [], ?float $timeout = null): Payload
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

        $payload = $this->payloadFactory->create($template, $tokens, $phpBinary, $timeout);

        if ($this->phpWrapper) {
            $payload->setWrapper($this->phpWrapper);
        }

        if ($this->phpConfig) {
            $payload->mergePhpConfig($this->phpConfig);
        }

        if (true === $this->phpDisableIni) {
            $payload->disableIni();
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
        /** @var string|null $phpBinary */
        $phpBinary = $this->finder->find($this->phpBinary);

        if (null === $phpBinary) {
            throw new \InvalidArgumentException(sprintf(
                'Could not find PHP binary "%s"', $this->phpBinary
            ));
        }

        return $phpBinary;
    }
}
