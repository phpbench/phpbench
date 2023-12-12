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
 *
 * @phpstan-type Stats array{stdev: array<int|float>, mean: array<int|float>, mode: array<int|float>, rstdev: array<int|float>, variance: array<int|float>, min: array<int|float>, max: array<int|float>, sum: array<int|float>}
 */
class Summary
{
    private int $nbSubjects = 0;
    private int $nbIterations = 0;
    private int $nbRejects = 0;
    private int $nbRevolutions = 0;
    private int $nbFailures = 0;
    private int $nbAssertions = 0;
    private int $nbErrors = 0;

    /** @var Stats */
    private array $stats = [
        'stdev' => [],
        'mean' => [],
        'mode' => [],
        'rstdev' => [],
        'variance' => [],
        'min' => [],
        'max' => [],
        'sum' => [],
    ];

    private bool $opCacheEnabled = false;

    private bool $xdebugEnabled = false;

    /**
     * @var string|null
     */
    private $phpVersion = null;

    /**
     */
    public function __construct(Suite $suite)
    {
        foreach ($suite->getBenchmarks() as $benchmark) {
            foreach ($benchmark->getSubjects() as $subject) {
                $this->nbSubjects++;

                foreach ($subject->getVariants() as $variant) {
                    $this->nbIterations += count($variant);
                    $this->nbRevolutions += $variant->getRevolutions();
                    $this->nbFailures += $variant->getAssertionResults()->failures()->count();
                    $this->nbAssertions += $variant->getAssertionResults()->count();
                    $this->nbErrors += $variant->getErrorStack()->count();
                    $this->nbRejects += $variant->getRejectCount();

                    if ($variant->hasErrorStack()) {
                        continue;
                    }

                    foreach ($variant->getStats()->getStats() as $name => $value) {
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

    public function getNbErrors(): int
    {
        return $this->nbErrors;
    }

    public function getNbAssertions(): int
    {
        return $this->nbAssertions;
    }

    /**
     * @return Stats
     */
    public function getStats(): array
    {
        return $this->stats;
    }

    /**
     * @return int|float
     */
    public function getMinTime()
    {
        return $this->stats['min'] ? min($this->stats['min']) : 0;
    }

    /**
     * @return int|float
     */
    public function getMaxTime()
    {
        return $this->stats['max'] ? min($this->stats['max']) : 0;
    }

    /**
     * @return int|float
     */
    public function getMeanTime()
    {
        return Statistics::mean($this->stats['mean']);
    }

    /**
     * @return int|float
     */
    public function getModeTime()
    {
        return Statistics::mean($this->stats['mode']);
    }

    /**
     * @return int|float
     */
    public function getTotalTime()
    {
        return array_sum($this->stats['sum']);
    }

    /**
     * @return int|float
     */
    public function getMeanStDev()
    {
        return Statistics::mean($this->stats['stdev']);
    }

    /**
     * @return int|float
     */
    public function getMeanRelStDev()
    {
        return Statistics::mean($this->stats['rstdev']);
    }

    public function getOpcacheEnabled(): bool
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
