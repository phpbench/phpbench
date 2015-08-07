<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Benchmark;

use PhpBench\ProgressLogger\NullProgressLogger;
use PhpBench\ProgressLogger;
use PhpBench\Result\SubjectResult;
use PhpBench\Result\IterationResult;
use PhpBench\Result\BenchmarkResult;
use PhpBench\Result\SuiteResult;
use PhpBench\Result\IterationsResult;
use PhpBench\Exception\InvalidArgumentException;
use PhpBench\Result\Loader\XmlLoader;
use PhpBench\BenchmarkInterface;
use PhpBench\ProgressLoggerInterface;

/**
 * The benchmark runner
 */
class Runner
{
    private $logger;
    private $collectionBuilder;
    private $subjectBuilder;
    private $subjectMemoryTotal;
    private $subjectLastMemoryInclusive;
    private $iterationsOverride;
    private $revsOverride;
    private $setUpTearDown = true;
    private $configPath;
    private $parametersOverride;
    private $subjectsOverride;
    private $groups;
    private $executor;

    /**
     * @param CollectionBuilder $collectionBuilder
     * @param SubjectBuilder $subjectBuilder
     * @param string $configPath
     */
    public function __construct(
        CollectionBuilder $collectionBuilder,
        SubjectBuilder $subjectBuilder,
        Executor $executor,
        $configPath
    ) {
        $this->logger = new NullProgressLogger();
        $this->collectionBuilder = $collectionBuilder;
        $this->subjectBuilder = $subjectBuilder;
        $this->executor = $executor;
        $this->configPath = $configPath;
    }

    /**
     * Whitelist of subject method names
     *
     * @param string[] $subjects
     */
    public function overrideSubjects(array $subjects)
    {
        $this->subjectsOverride = $subjects;
    }

    /**
     * Set the progress logger to use
     *
     * @param ProgressLoggerInterface
     */
    public function setProgressLogger(ProgressLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Call to disable the setUp and tearDown methods
     */
    public function disableSetup()
    {
        $this->setUpTearDown = false;
    }

    /**
     * Override the number of iterations to execute
     *
     * @param integer $iterations
     */
    public function overrideIterations($iterations)
    {
        $this->iterationsOverride = $iterations;
    }

    /**
     * Override the number of rev(olutions) to run
     *
     * @param integer
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
     * Whitelist of groups to execute
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
     * Run all benchmarks (or all applicable benchmarks) in the given path
     *
     * @param string
     */
    public function runAll($path)
    {
        $collection = $this->collectionBuilder->buildCollection($path);
        $benchmarkResults = array();

        foreach ($collection->getBenchmarks() as $benchmark) {
            $this->logger->benchmarkStart($benchmark);
            $benchmarkResults[] = $this->run($benchmark);
            $this->logger->benchmarkEnd($benchmark);
        }

        $benchmarkSuiteResult = new SuiteResult($benchmarkResults);

        return $benchmarkSuiteResult;
    }

    private function run(BenchmarkInterface $benchmark)
    {
        $subjects = $this->subjectBuilder->buildSubjects($benchmark, $this->subjectsOverride, $this->groups, $this->parametersOverride);
        $subjectResults = array();

        foreach ($subjects as $subject) {
            $this->logger->subjectStart($subject);
            $subjectResults[] = $this->runSubject($benchmark, $subject);
            $this->logger->subjectEnd($subject);
        }

        if (true === $this->setUpTearDown && method_exists($benchmark, 'tearDown')) {
            $benchmark->tearDown();
        }

        $benchmarkResult = new BenchmarkResult(get_class($benchmark), $subjectResults);

        return $benchmarkResult;
    }

    private function runSubject(BenchmarkInterface $benchmark, Subject $subject)
    {
        $this->subjectMemoryTotal = 0;
        $this->subjectLastMemoryInclusive = memory_get_usage();
        $iterationCount = null === $this->iterationsOverride ? $subject->getNbIterations() : $this->iterationsOverride;
        $revolutionCounts = $this->revsOverride ? array($this->revsOverride) : $subject->getRevs();

        $iterationsResults = array();

        $iterationsResults[] = $this->runIterations($benchmark, $subject, $iterationCount, $revolutionCounts);

        $subjectResult = new SubjectResult(
            $subject->getIdentifier(),
            $subject->getMethodName(),
            $subject->getGroups(),
            $subject->getParameters(),
            $iterationsResults
        );

        return $subjectResult;
    }

    private function runIterations(BenchmarkInterface $benchmark, Subject $subject, $iterationCount, array $revolutionCounts)
    {
        $iterationsResult = array();

        for ($index = 0; $index < $iterationCount; $index++ ) {
            foreach ($revolutionCounts as $revolutionCount) {
                $iterationsResult[] = $this->runIteration($benchmark, $subject, $revolutionCount);
            }
        }

        return new IterationsResult($iterationsResult, $subject->getParameters());
    }

    private function runIteration(BenchmarkInterface $benchmark, Subject $subject, $revolutionCount)
    {
        $result = $this->executor->execute(
            $benchmark,
            $subject->getMethodName(),
            $revolutionCount,
            $subject->getBeforeMethods()
        );

        $result['revs'] = $revolutionCount;
        return new IterationResult($result);
    }
}
