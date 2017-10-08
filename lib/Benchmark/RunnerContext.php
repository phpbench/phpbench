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

/**
 * The benchmark runner context.
 */
class RunnerContext
{
    private $path;
    private $options;

    /**
     * @param string $path
     * @param mixed[] $options
     */
    public function __construct($path, array $options = [])
    {
        if (null === $path) {
            throw new \InvalidArgumentException(
                'You must either specify or configure a path where your benchmarks can be found.'
            );
        }

        $this->path = $path;

        $defaultOptions = [
            'executor' => 'microtime',
            'context_name' => null,
            'filters' => [],
            'groups' => [],
            'iterations' => null,
            'revolutions' => null,
            'parameters' => null,
            'retry_threshold' => null,
            'sleep' => null,
            'warmup' => null,
            'output_time_precision' => null,
            'output_time_unit' => null,
            'stop_on_error' => null,
            'assertions' => null,
        ];

        $options = array_merge(
            $defaultOptions,
            $options
        );

        $diff = array_diff(array_keys($options), array_keys($defaultOptions));

        if ($diff) {
            throw new \InvalidArgumentException(sprintf(
                'Invalid options "%s" given to runner context, valid options "%s"',
                implode('", "', $diff),
                implode('", "', array_keys($defaultOptions))
            ));
        }

        $numericValidator = function ($context, $v) {
            if (!is_numeric($v)) {
                throw new \InvalidArgumentException(sprintf(
                    sprintf(
                        '%s must be a number, "%s" given',
                        $context, is_object($v) ? get_class($v) : gettype($v)
                    )
                ));
            }
        };
        $isArrayValidator = function ($context, $v) {
            if (!is_array($v)) {
                throw new \InvalidArgumentException(sprintf(
                    sprintf(
                        '%s must be an array, "%s" given',
                        $context, is_object($v) ? get_class($v) : gettype($v)
                    )
                ));
            }
        };

        $validators = [
            'retry_threshold' => function ($v) use ($numericValidator) {
                if (null === $v) {
                    return;
                }

                $numericValidator('Retry threshold', $v);

                if ($v <= 0) {
                    throw new \InvalidArgumentException(sprintf(
                        'Retry threshold must be greater than 0, "%s" given',
                        $v
                    ));
                }
            },
            'sleep' => function ($v) use ($numericValidator) {
                if (null === $v) {
                    return;
                }

                $numericValidator('Retry threshold', $v);
            },
            'iterations' => function ($v) use ($numericValidator, $isArrayValidator) {
                if (null === $v) {
                    return;
                }

                $isArrayValidator('Iterations', $v);
                foreach ($v as $iterations) {
                    $numericValidator('Iterations', $iterations);
                }
            },
            'revolutions' => function ($v) use ($numericValidator, $isArrayValidator) {
                if (null === $v) {
                    return;
                }

                $isArrayValidator('Revolutions', $v);

                foreach ($v as $revs) {
                    $numericValidator('Revolutions', $revs);
                }
            },
            'warmup' => function ($v) use ($numericValidator, $isArrayValidator) {
                if (null === $v) {
                    return;
                }

                $isArrayValidator('Warmup', $v);

                foreach ($v as $warmup) {
                    $numericValidator('Warmup', $warmup);
                }
            },
        ];

        foreach ($validators as $optionName => $validator) {
            $validator($options[$optionName]);
        }

        $this->options = $options;
    }

    /**
     * Return the path underwhich to scan for benchmarks.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
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
        return $this->options['context_name'];
    }

    /**
     * Whitelist of subject method names.
     *
     * @param string[] $subjects
     */
    public function getFilters()
    {
        return $this->options['filters'];
    }

    /**
     * Override the number of iterations to execute.
     *
     * @return int
     */
    public function getIterations($default = null)
    {
        return $this->options['iterations'] ?: $default;
    }

    /**
     * Get the number of rev(olutions) to run.
     *
     * @param int $default
     */
    public function getRevolutions($default = null)
    {
        return $this->options['revolutions'] ?: $default;
    }

    /**
     * Return the number of warmup revolutions that should be exectuted.
     *
     * @param int $default
     */
    public function getWarmup($default = null)
    {
        return $this->options['warmup'] ?: $default;
    }

    /**
     * Override parameters.
     *
     * @return mixed[]
     */
    public function getParameterSets($default = null)
    {
        $parameters = $this->options['parameters'] ? [[$this->options['parameters']]] : $default;

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
        if (null === $this->options['sleep']) {
            return $default;
        }

        return $this->options['sleep'];
    }

    /**
     * Whitelist of groups to execute.
     *
     * @return string[]
     */
    public function getGroups()
    {
        return $this->options['groups'];
    }

    /**
     * Get the deviation threshold beyond which the iteration should
     * be retried.
     *
     * A value of NULL will disable retry.
     *
     * @return float
     */
    public function getRetryThreshold($default = null)
    {
        return $this->options['retry_threshold'] ?: $default;
    }

    /**
     * Return the output time unit.
     *
     * @return string
     */
    public function getOutputTimeUnit($default = null)
    {
        return $this->options['output_time_unit'] ?: $default;
    }

    /**
     * Return the output time precision.
     *
     * @return string
     */
    public function getOutputTimePrecision($default = null)
    {
        return $this->options['output_time_precision'] ?: $default;
    }

    /**
     * Return either an executor configuration name or an actual configuration.
     *
     * @return string|array
     */
    public function getExecutor()
    {
        return $this->options['executor'];
    }

    /**
     * Return true if the runner should exit on the first exception encountered.
     *
     * @retrun bool
     */
    public function getStopOnError()
    {
        return $this->options['stop_on_error'];
    }

    /**
     * Return assertions (which will override any metadata based assertions).
     */
    public function getAssertions(): array
    {
        return $this->options['assertions'] ?: [];
    }
}
