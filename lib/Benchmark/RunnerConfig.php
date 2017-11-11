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

namespace PhpBench\Benchmark;

use PhpBench\Benchmark\RunnerConfig;

/**
 * The benchmark runner context.
 */
class RunnerConfig
{
    /**
     * @var string
     */
    private $executor;

    /**
     * @var string
     */
    private $contextName;

    /**
     * @var array
     */
    private $filters;

    /**
     * @var array
     */
    private $groups;

    /**
     * @var int
     */
    private $iterations;

    /**
     * @var int
     */
    private $revolutions;

    /**
     * @var float
     */
    private $retryThreshold;

    /**
     * @var int
     */
    private $sleep;

    /**
     * @var int
     */
    private $warmup;

    /**
     * @var int
     */
    private $outputTimePrecision;

    /**
     * @var string
     */
    private $outputTimeUnit;

    /**
     * @var bool
     */
    private $stopOnError;

    /**
     * @var array
     */
    private $assertions;

    /**
     * @var array
     */
    private $parameters = [];

    public static function create(): RunnerConfig
    {
        return new RunnerConfig();
    }

    public function merge(RunnerConfig $config): RunnerConfig
    {
        $new = clone $this;

        foreach ($config as $property => $value) {
            if (null !== $value) {
                $new->$property = $value;
            }
        }

        return $new;
    }

    /**
     * Return the name to assign to this suite.
     *
     * NOTE: Do not confuse this with
     *       this context class. It is simply an arbitrary identifier to identify the
     *       suite when doing a comparison.
     *
     * @return string
     */
    public function getContextName()
    {
        return $this->contextName;
    }

    /**
     * Whitelist of subject method names.
     *
     * @param string[] $subjects
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Override the number of iterations to execute.
     *
     * @return array
     */
    public function getIterations($default = null)
    {
        return $this->iterations ?: $default;
    }

    /**
     * Get the number of rev(olutions) to run.
     *
     * @param int $default
     */
    public function getRevolutions($default = null)
    {
        return $this->revolutions ?: $default;
    }

    /**
     * Return the number of warmup revolutions that should be exectuted.
     *
     * @param int $default
     */
    public function getWarmup($default = null)
    {
        return $this->warmup ?: $default;
    }

    /**
     * Override parameters.
     *
     * @return mixed[]
     */
    public function getParameterSets($default = null)
    {
        $parameters = $this->parameters ? [[$this->parameters]] : $default;

        if (!$parameters) {
            return [[[]]];
        }

        return $parameters;
    }

    /**
     * Override the sleep interval (in microseconds).
     *
     * @param int $sleep
     */
    public function getSleep($default = null)
    {
        if (null === $this->sleep) {
            return $default;
        }

        return $this->sleep;
    }

    /**
     * Whitelist of groups to execute.
     *
     * @return string[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Get the deviation threshold beyond which the iteration should
     * be retried.
     *
     * A value of NULL will disable retry.
     *
     * @return float
     */
    public function getRetryThreshold(float $default = null)
    {
        return $this->retryThreshold ?: $default;
    }

    /**
     * Return the output time unit.
     *
     * @return string
     */
    public function getOutputTimeUnit(string $default = null)
    {
        return $this->outputTimeUnit ?: $default;
    }

    /**
     * Return the output time precision.
     *
     * @return string
     */
    public function getOutputTimePrecision(string $default = null)
    {
        return $this->outputTimePrecision ?: $default;
    }

    /**
     * Return either an executor configuration name or an actual configuration.
     *
     * @return string
     */
    public function getExecutor()
    {
        return $this->executor;
    }

    /**
     * Return true if the runner should exit on the first exception encountered.
     *
     * @retrun bool
     */
    public function getStopOnError()
    {
        return $this->stopOnError;
    }

    /**
     * Return assertions (which will override any metadata based assertions).
     */
    public function getAssertions(): array
    {
        return $this->assertions ?: [];
    }

    public function withExecutor(string $executor = null): RunnerConfig
    {
        $new = clone $this;
        $new->executor = $executor;

        return $new;
    }

    public function withContextName(string $contextName = null): RunnerConfig
    {
        $new = clone $this;
        $new->contextName = $contextName;

        return $new;
    }

    public function withFilters(array $filters = null): RunnerConfig
    {
        $new = clone $this;
        $new->filters = $filters;

        return $new;
    }

    public function withGroups(array $groups = null): RunnerConfig
    {
        $new = clone $this;
        $new->groups = $groups;

        return $new;
    }

    public function withIterations(array $iterations = null): RunnerConfig
    {
        $new = clone $this;
        $new->iterations = $iterations;

        return $new;
    }

    public function withRevolutions(array $revolutions = null): RunnerConfig
    {
        $new = clone $this;
        $new->revolutions = $revolutions;

        return $new;
    }

    public function withParameters(array $parameters = null): RunnerConfig
    {
        $new = clone $this;
        $new->parameters = $parameters;

        return $new;
    }

    public function withRetryThreshold(float $retryThreshold = null): RunnerConfig
    {
        $new = clone $this;
        $new->retryThreshold = $retryThreshold;

        return $new;
    }

    public function withSleep(int $sleep = null): RunnerConfig
    {
        $new = clone $this;
        $new->sleep = $sleep;

        return $new;
    }

    public function withWarmup(int $warmup = null): RunnerConfig
    {
        $new = clone $this;
        $new->warmup = $warmup;

        return $new;
    }

    public function withOutputTimePrecision(int $outputTimePrecision = null): RunnerConfig
    {
        $new = clone $this;
        $new->outputTimePrecision = $outputTimePrecision;

        return $new;
    }

    public function withOutputTimeUnit(string $outputTimeUnit = null): RunnerConfig
    {
        $new = clone $this;
        $new->outputTimeUnit = $outputTimeUnit;

        return $new;
    }

    public function withStopOnError(bool $stopOnError = null): RunnerConfig
    {
        $new = clone $this;
        $new->stopOnError = $stopOnError;

        return $new;
    }

    public function withAssertions(array $assertions = null): RunnerConfig
    {
        $new = clone $this;
        $new->assertions = $assertions;

        return $new;
    }
}
