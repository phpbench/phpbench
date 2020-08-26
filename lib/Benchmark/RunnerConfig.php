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

use InvalidArgumentException;
use PhpBench\Model\SuiteCollection;

/**
 * The benchmark runner context.
 */
class RunnerConfig
{
    /**
     * @var string
     */
    private $executor = 'microtime';

    /**
     * @var string
     */
    private $tag;

    /**
     * @var array<string>
     */
    private $filters = [];

    /**
     * @var string[]
     */
    private $groups = [];

    /**
     * @var int[]
     */
    private $iterations = [];

    /**
     * @var int[]
     */
    private $revolutions = [];

    /**
     * @var float
     */
    private $retryThreshold;

    /**
     * @var int
     */
    private $sleep;

    /**
     * @var int[]
     */
    private $warmup = [];

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
     * @var array<string>
     */
    private $assertions;

    /**
     * @var array<string,mixed>
     */
    private $parameters = [];

    /**
     * @var SuiteCollection
     */
    private $baselines;

    private function __construct()
    {
        $this->baselines = new SuiteCollection();
    }

    public static function create(): self
    {
        return new self();
    }

    public function merge(self $config): self
    {
        $default = new self();
        $new = clone $this;

        /** @phpstan-ignore-next-line Phpstan doesn't understand this
            and it's rather ugly in anycase */
        foreach ($config as $property => $value) {
            if ($value !== $default->$property) {
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
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * Whitelist of subject method names.
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
     * @param array $default
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
     * @param mixed $default
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

    public function withExecutor($executor = null): self
    {
        $new = clone $this;
        $new->executor = $executor;

        return $new;
    }

    public function withTag(string $tag = null): self
    {
        $new = clone $this;
        $new->tag = $tag;

        return $new;
    }

    public function withFilters(array $filters = null): self
    {
        $new = clone $this;
        $new->filters = $filters;

        return $new;
    }

    public function withGroups(array $groups = null): self
    {
        $new = clone $this;
        $new->groups = $groups;

        return $new;
    }

    public function withIterations(array $iterations = null): self
    {
        $this->assertArrayValuesGreaterThanZero($iterations);

        $new = clone $this;
        $new->iterations = $iterations;

        return $new;
    }

    public function withRevolutions(array $revolutions = null): self
    {
        $this->assertArrayValuesGreaterThanZero('revs', $revolutions);

        $new = clone $this;
        $new->revolutions = $revolutions;

        return $new;
    }

    public function withParameters(array $parameters = null): self
    {
        $new = clone $this;
        $new->parameters = $parameters;

        return $new;
    }

    public function withRetryThreshold(float $retryThreshold = null): self
    {
        $this->assertGreaterThanZero('retry threshold', $retryThreshold);

        $new = clone $this;
        $new->retryThreshold = $retryThreshold;

        return $new;
    }

    public function withSleep(int $sleep = null): self
    {
        $this->assertGreaterThanZero('sleep', $sleep);

        $new = clone $this;
        $new->sleep = $sleep;

        return $new;
    }

    public function withWarmup(array $warmup = null): self
    {
        $this->assertArrayValuesGreaterThanZero('warmup', $warmup);

        $new = clone $this;
        $new->warmup = $warmup;

        return $new;
    }

    public function withOutputTimePrecision(int $outputTimePrecision = null): self
    {
        $new = clone $this;
        $new->outputTimePrecision = $outputTimePrecision;

        return $new;
    }

    public function withOutputTimeUnit(string $outputTimeUnit = null): self
    {
        $new = clone $this;
        $new->outputTimeUnit = $outputTimeUnit;

        return $new;
    }

    public function withStopOnError(bool $stopOnError = null): self
    {
        $new = clone $this;
        $new->stopOnError = $stopOnError;

        return $new;
    }

    public function withAssertions(array $assertions = null): self
    {
        $new = clone $this;
        $new->assertions = $assertions;

        return $new;
    }

    public function withBaselines(SuiteCollection $baselines): self
    {
        $new = clone($this);
        $new->baselines = $baselines;

        return $new;
    }

    public function getBaselines(): SuiteCollection
    {
        return $this->baselines;
    }

    private function assertArrayValuesGreaterThanZero($field, array $values = [])
    {
        $values = array_filter($values, function ($value) {
            return $value <= 0;
        });

        if (empty($values)) {
            return;
        }

        throw new InvalidArgumentException(sprintf(
            'All values for "%s" must be greater than 0, the following were less than 0 "%s"',
            $field, implode('", "', $values)
        ));
    }

    private function assertGreaterThanZero(string $field, float $value = null)
    {
        if (null === $value) {
            return;
        }

        if ($value > 0) {
            return;
        }

        throw new InvalidArgumentException(sprintf(
            '"%s" must be greater than 0, got "%s"',
            $field, $value
        ));
    }
}
