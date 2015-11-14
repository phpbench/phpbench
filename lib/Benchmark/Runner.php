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

use PhpBench\Benchmark\Metadata\BenchmarkMetadata;
use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\PhpBench;
use PhpBench\Progress\Logger\NullLogger;
use PhpBench\Progress\LoggerInterface;

/**
 * The benchmark runner.
 */
class Runner
{
    private $logger;
    private $collectionBuilder;
    private $iterationsOverride;
    private $revsOverride;
    private $executorOverride;
    private $configPath;
    private $parametersOverride;
    private $sleepOverride;
    private $filters = array();
    private $groups = array();
    private $executor;
    private $retryThreshold = null;

    /**
     * @param CollectionBuilder $collectionBuilder
     * @param SubjectBuilder $subjectBuilder
     * @param string $configPath
     */
    public function __construct(
        CollectionBuilder $collectionBuilder,
        ExecutorInterface $executor,
        $retryThreshold,
        $configPath
    ) {
        $this->logger = new NullLogger();
        $this->collectionBuilder = $collectionBuilder;
        $this->executor = $executor;
        $this->configPath = $configPath;
        $this->retryThreshold = $retryThreshold;
    }

    /**
     * Whitelist of subject method names.
     *
     * @param string[] $subjects
     */
    public function setFilters(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * Set the progress logger to use.
     *
     * @param LoggerInterface
     */
    public function setProgressLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Override the number of iterations to execute.
     *
     * @param int $iterations
     */
    public function overrideIterations($iterations)
    {
        $this->iterationsOverride = $iterations;
    }

    /**
     * Override the number of rev(olutions) to run.
     *
     * @param int
     */
    public function overrideRevs($revs)
    {
        $this->revsOverride = $revs;
    }

    public function overrideParameters($parameters)
    {
        $this->parametersOverride = $parameters;
    }

    /**
     * Override the executor to use.
     *
     * @param string $executor
     */
    public function overrideExecutor($executor)
    {
        $this->executorOverride = $executor;
    }

    /**
     * Override the sleep interval (in microseconds).
     *
     * @param int $sleep
     */
    public function overrideSleep($sleep)
    {
        return $this->sleepOverride = $sleep;
    }

    /**
     * Whitelist of groups to execute.
     *
     * @param string[]
     */
    public function setGroups(array $groups)
    {
        $this->groups = $groups;
    }

    /**
     * Set the path to the configuration file.
     * This is required when launching a new process.
     *
     * @param string $configPath
     */
    public function setConfigPath($configPath)
    {
        $this->configPath = $configPath;
    }

    /**
     * Set the deviation threshold beyond which the iteration should
     * be retried.
     *
     * A value of NULL will disable retry.
     *
     * @param float $retryThreshold
     */
    public function setRetryThreshold($retryThreshold)
    {
        if (!is_numeric($retryThreshold)) {
            throw new \InvalidArgumentException(sprintf(
                'Retry threshold must be numeric, got "%s"',
                $retryThreshold
            ));
        }

        $this->retryThreshold = $retryThreshold;
    }

    /**
     * Run all benchmarks (or all applicable benchmarks) in the given path.
     *
     * The $name argument will set the "name" attribute on the "suite" element.
     *
     * @param string $contextName
     * @param string $path
     */
    public function runAll($contextName, $path)
    {
        $dom = new SuiteDocument();
        $rootEl = $dom->createElement('phpbench');
        $rootEl->setAttribute('version', PhpBench::VERSION);
        $suiteEl = $rootEl->appendElement('suite');

        $suiteEl->setAttribute('context', $contextName);

        $suiteEl->setAttribute('date', date('c'));

        if ($this->retryThreshold) {
            $suiteEl->setAttribute('retry-threshold', $this->retryThreshold);
        }

        $collection = $this->collectionBuilder->buildCollection($path, $this->filters, $this->groups);

        /* @var BenchmarkMetadata */
        foreach ($collection->getBenchmarks() as $benchmark) {
            $benchmarkEl = $dom->createElement('benchmark');
            $benchmarkEl->setAttribute('class', $benchmark->getClass());

            $this->logger->benchmarkStart($benchmark);
            $this->run($benchmark, $benchmarkEl);
            $this->logger->benchmarkEnd($benchmark);

            $suiteEl->appendChild($benchmarkEl);
        }

        $dom->appendChild($rootEl);

        return $dom;
    }

    private function run(BenchmarkMetadata $benchmark, \DOMElement $benchmarkEl)
    {
        if ($benchmark->getBeforeClassMethods()) {
            $this->executor->executeMethods($benchmark, $benchmark->getBeforeClassMethods());
        }

        foreach ($benchmark->getSubjectMetadatas() as $subject) {
            $subjectEl = $benchmarkEl->appendElement('subject');
            $subjectEl->setAttribute('name', $subject->getName());

            if (true === $subject->getSkip()) {
                continue;
            }

            foreach ($subject->getGroups() as $group) {
                $groupEl = $subjectEl->appendElement('group');
                $groupEl->setAttribute('name', $group);
            }

            $this->logger->subjectStart($subject);
            $this->runSubject($subject, $subjectEl);
            $this->logger->subjectEnd($subject);
        }

        if ($benchmark->getAfterClassMethods()) {
            $this->executor->executeMethods($benchmark, $benchmark->getAfterClassMethods());
        }
    }

    private function runSubject(SubjectMetadata $subject, \DOMElement $subjectEl)
    {
        $iterationCount = null === $this->iterationsOverride ? $subject->getIterations() : $this->iterationsOverride;
        $revolutionCounts = $this->revsOverride ? array($this->revsOverride) : $subject->getRevs();
        $parameterSets = $this->parametersOverride ? array(array($this->parametersOverride)) : $subject->getParameterSets() ?: array(array(array()));
        $paramsIterator = new CartesianParameterIterator($parameterSets);

        foreach ($paramsIterator as $parameters) {
            $variantEl = $subjectEl->ownerDocument->createElement('variant');
            $variantEl->setAttribute('sleep', $this->getSleepInterval($subject->getSleep()));
            foreach ($parameters as $name => $value) {
                $parameterEl = $this->createParameter($subjectEl, $name, $value);
                $variantEl->appendChild($parameterEl);
            }

            $subjectEl->appendChild($variantEl);
            $this->runIterations($subject, $iterationCount, (array) $revolutionCounts, $parameters, $variantEl);
        }
    }

    private function createParameter($parentEl, $name, $value)
    {
        $parameterEl = $parentEl->ownerDocument->createElement('parameter');
        $parameterEl->setAttribute('name', $name);

        if (is_array($value)) {
            $parameterEl->setAttribute('type', 'collection');
            foreach ($value as $key => $element) {
                $childEl = $this->createParameter($parameterEl, $key, $element);
                $parameterEl->appendChild($childEl);
            }

            return $parameterEl;
        }

        if (is_scalar($value)) {
            $parameterEl->setAttribute('value', $value);

            return $parameterEl;
        }

        throw new \InvalidArgumentException(sprintf(
            'Parameters must be either scalars or arrays, got: %s',
            is_object($value) ? get_class($value) : gettype($value)
        ));
    }

    private function runIterations(SubjectMetadata $subject, $iterationCount, array $revolutionCounts, ParameterSet $parameterSet, \DOMElement $variantEl)
    {
        $iterationCollection = new IterationCollection($this->retryThreshold);
        for ($index = 0; $index < $iterationCount; $index++) {
            foreach ($revolutionCounts as $revolutionCount) {
                $iteration = new Iteration($index, $subject, $revolutionCount, $parameterSet);
                $this->runIteration($iteration, $subject->getSleep());

                $iterationCollection->add($iteration);
            }
        }

        $iterationCollection->computeDeviations();

        while ($iterationCollection->getRejectCount() > 0) {
            $this->logger->retryStart($iterationCollection->getRejectCount());
            foreach ($iterationCollection->getRejects() as $reject) {
                $reject->incrementRejectionCount();
                $this->runIteration($reject, $subject->getSleep());
            }
            $iterationCollection->computeDeviations();
        }

        foreach ($iterationCollection as $iteration) {
            $iterationEl = $variantEl->ownerDocument->createElement('iteration');
            $iterationEl->setAttribute('revs', $iteration->getRevolutions());
            $iterationEl->setAttribute('time', $iteration->getResult()->getTime());
            $iterationEl->setAttribute('memory', $iteration->getResult()->getMemory());
            $iterationEl->setAttribute('deviation', $iteration->getDeviation());
            $iterationEl->setAttribute('rejection-count', $iteration->getRejectionCount());
            $variantEl->appendChild($iterationEl);
        }
    }

    public function runIteration(Iteration $iteration, $sleep)
    {
        $this->logger->iterationStart($iteration);
        $result = $this->executor->execute($iteration);
        $sleep = $this->getSleepInterval($sleep);

        if ($sleep) {
            usleep($sleep);
        }

        $iteration->setResult($result);
        $this->logger->iterationEnd($iteration);
    }

    /**
     * Utility function to return the correct sleep interval
     * in case that the sleep interval has been overridden.
     *
     * @param int $sleep
     *
     * @return int
     */
    private function getSleepInterval($sleep)
    {
        return null !== $this->sleepOverride ? $this->sleepOverride : $sleep;
    }
}
