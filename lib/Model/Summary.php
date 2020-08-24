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

use PhpBench\Math\Statistics;

/**
 * Provides summary statistics for the entires suite.
 */
class Summary
{
    private $nbSubjects = 0;
    private $nbIterations = 0;
    private $nbRejects = 0;
    private $nbRevolutions = 0;
    private $nbFailures = 0;
    private $nbWarnings = 0;
    private $stats = [
        'stdev' => [],
        'mean' => [],
        'mode' => [],
        'rstdev' => [],
        'variance' => [],
        'min' => [],
        'max' => [],
        'sum' => [],
    ];
    private $errorStacks = [];

    /**
     * @var bool
     */
    private $opCacheEnabled = false;

    /**
     * @var bool
     */
    private $xdebugEnabled = false;

    /**
     * @var string
     */
    private $phpVersion = null;

    /**
     * @param Suite $suite
     */
    public function __construct(Suite $suite)
    {
        foreach ($suite->getBenchmarks() as $benchmark) {
            foreach ($benchmark->getSubjects() as $subject) {
                $this->nbSubjects++;

                foreach ($subject->getVariants() as $variant) {
                    $this->nbIterations += count($variant);
                    $this->nbRevolutions += $variant->getRevolutions();
                    $this->nbFailures += count($variant->getFailures());
                    $this->nbWarnings += count($variant->getWarnings());
                    $this->nbRejects += $variant->getRejectCount();

                    if ($variant->hasErrorStack()) {
                        $this->errorStacks[] = $variant->getErrorStack();

                        continue;
                    }

                    foreach ($variant->getStats() as $name => $value) {
                        $this->stats[$name][] = $value;
                    }
                }
            }
        }

        $env = $suite->getEnvInformations();

        if (isset($env['opcache'])) {
            $this->opCacheEnabled = (bool)($env['opcache']['enabled'] ?? false);
        }

        if (isset($env['php'])) {
            $this->xdebugEnabled = (bool)($env['php']['xdebug'] ?? false);
            $this->phpVersion = $env['php']['version'] ?? null;
        }
    }

    public function getNbSubjects(): int
    {
        return $this->nbSubjects;
    }

    public function getNbIterations(): int
    {
        return $this->nbIterations;
    }

    public function getNbRejects(): int
    {
        return $this->nbRejects;
    }

    public function getNbRevolutions(): int
    {
        return $this->nbRevolutions;
    }

    public function getNbFailures(): int
    {
        return $this->nbFailures;
    }

    public function getNbWarnings(): int
    {
        return $this->nbWarnings;
    }

    public function getStats(): array
    {
        return $this->stats;
    }

    public function getMinTime()
    {
        return $this->stats['min'] ? min($this->stats['min']) : 0;
    }

    public function getMaxTime()
    {
        return $this->stats['max'] ? min($this->stats['max']) : 0;
    }

    public function getMeanTime()
    {
        return Statistics::mean($this->stats['mean']);
    }

    public function getModeTime()
    {
        return Statistics::mean($this->stats['mode']);
    }

    public function getTotalTime()
    {
        return array_sum($this->stats['sum']);
    }

    public function getMeanStDev()
    {
        return Statistics::mean($this->stats['stdev']);
    }

    public function getMeanRelStDev()
    {
        return Statistics::mean($this->stats['rstdev']);
    }

    public function getOpcacheEnabled(): ?bool
    {
        return $this->opCacheEnabled;
    }

    public function getXdebugEnabled(): bool
    {
        return $this->xdebugEnabled;
    }

    public function getPhpVersion(): ?string
    {
        return $this->phpVersion;
    }
}
