<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
    public function __construct($path, array $options = array())
    {
        if (null === $path) {
            throw new \InvalidArgumentException(
                'You must either specify or configure a path where your benchmarks can be found.'
            );
        }

        $this->path = $path;

        $defaultOptions = array(
            'executor' => 'microtime',
            'context_name' => null,
            'filters' => array(),
            'groups' => array(),
            'iterations' => null,
            'revolutions' => null,
            'parameters' => null,
            'retry_threshold' => null,
            'sleep' => null,
        );

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

        $validators = array(
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
            'iterations' => function ($v) use ($numericValidator) {
                if (null === $v) {
                    return;
                }

                $numericValidator('Iterations', $v);
            },
            'revolutions' => function ($v) use ($numericValidator) {
                if (null === $v) {
                    return;
                }

                $numericValidator('Revolutions', $v);
            },
        );

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
     * Override the number of rev(olutions) to run.
     *
     * @param int
     */
    public function getRevolutions($default = null)
    {
        return $this->options['revolutions'] ?: $default;
    }

    /**
     * Override parameters.
     *
     * @return mixed[]
     */
    public function getParameterSets($default = null)
    {
        $parameters = $this->options['parameters'] ? array(array($this->options['parameters'])) : $default;

        if (!$parameters) {
            return array(array(array()));
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
     * Return either an executor configuration name or an actual configuration.
     *
     * @return string|array
     */
    public function getExecutor()
    {
        return $this->options['executor'];
    }
}
