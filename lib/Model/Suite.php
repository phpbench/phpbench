<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Model;

use PhpBench\Environment\Information;

/**
 * Represents a Suite.
 *
 * This is the base of the object graph created by the Runner.
 */
class Suite implements \IteratorAggregate
{
    private $contextName;
    private $date;
    private $configPath;
    private $envInformations = array();
    private $benchmarks = array();

    /**
     * __construct.
     *
     * @param array $benchmarks
     * @param string $contextName
     * @param \DateTime $date
     * @param string $configPath
     * @param Information[] $envInformations
     */
    public function __construct(
        $contextName,
        \DateTime $date,
        $configPath = null,
        array $benchmarks = array(),
        array $envInformations = array()
    ) {
        $this->contextName = $contextName;
        $this->date = $date;
        $this->configPath = $configPath;
        $this->envInformations = $envInformations;
        $this->benchmarks = $benchmarks;
    }

    public function getBenchmarks()
    {
        return $this->benchmarks;
    }

    /**
     * Create and add a benchmark.
     *
     * @param string $class
     *
     * @return Benchmark
     */
    public function createBenchmark($class)
    {
        $benchmark = new Benchmark($this, $class);
        $this->benchmarks[] = $benchmark;

        return $benchmark;
    }

    public function getIterator()
    {
        return new \ArrayObject($this->benchmarks);
    }

    public function getContextName()
    {
        return $this->contextName;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getConfigPath()
    {
        return $this->configPath;
    }

    public function getSummary()
    {
        return new Summary($this);
    }

    public function getIterations()
    {
        $iterations = array();

        foreach ($this->getVariants() as $variant) {
            foreach ($variant as $iteration) {
                $iterations[] = $iteration;
            }
        }

        return $iterations;
    }

    public function getSubjects()
    {
        $subjects = array();

        foreach ($this->getBenchmarks() as $benchmark) {
            foreach ($benchmark->getSubjects() as $subject) {
                $subjects[] = $subject;
            }
        }

        return $subjects;
    }

    public function getVariants()
    {
        $variants = array();
        foreach ($this->getSubjects() as $subject) {
            foreach ($subject->getVariants() as $variant) {
                $variants[] = $variant;
            }
        }

        return $variants;
    }

    public function getErrorStacks()
    {
        $errorStacks = array();

        foreach ($this->getVariants() as $variant) {
            if (false === $variant->hasErrorStack()) {
                continue;
            }

            $errorStacks[] = $variant->getErrorStack();
        }

        return $errorStacks;
    }

    /**
     * @param Information[]
     */
    public function setEnvInformations(array $envInformations)
    {
        $this->envInformations = $envInformations;
    }

    /**
     * @return Information[]
     */
    public function getEnvInformations()
    {
        return $this->envInformations;
    }
}
