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

use RuntimeException;
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
     * @var ExecutableFinder
     */
    private $finder;

    /**
     * @var bool
     */
    private $phpDisableIni;

    public function __construct(
        ExecutableFinder $finder = null,
        ?string $bootstrap = null,
        ?string $phpBinary = null,
        array $phpConfig = [],
        ?string $phpWrapper = null,
        bool $phpDisableIni = false
    ) {
        $this->bootstrap = $bootstrap;
        $this->phpBinary = $phpBinary;
        $this->phpConfig = $phpConfig;
        $this->phpWrapper = $phpWrapper;
        $this->finder = $finder ?: new ExecutableFinder();
        $this->phpDisableIni = $phpDisableIni;
    }

    public function payload(string $templatePath, array $tokens = []): PayloadBuilder
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

        $builder = new PayloadBuilder($templatePath, $tokens);
        $builder->withPhpBinary($this->resolvePhpBinary());
        $builder->withPhpWrapper($this->phpWrapper);
        $builder->includePhpConfig($this->phpConfig);

        if (true === $this->phpDisableIni) {
            $builder->disableIni();
        }

        return $builder;
    }

    private function resolvePhpBinary(): string
    {
        // if no php binary, use the PhpExecutableFinder (generally will
        // resolve to PHP_BINARY)
        if (!$this->phpBinary) {
            $finder = new PhpExecutableFinder();

            $bin = $finder->find();
            if (!$bin) {
                throw new RuntimeException(
                    'Could not resolve a PHP binary on this system'
                );
            }

            return $bin;
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
