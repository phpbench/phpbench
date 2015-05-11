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
use PhpBench\Benchmark\CollectionBuilder;
use PhpBench\Benchmark;
use PhpBench\Result\SubjectResult;
use PhpBench\Benchmark\Subject;
use PhpBench\Benchmark\Iteration;
use PhpBench\Result\IterationResult;
use PhpBench\Result\IterationsResults;
use PhpBench\Result\BenchmarkResult;
use PhpBench\Result\SuiteResult;

class Runner
{
    private $logger;
    private $finder;
    private $subjectBuilder;
    private $subjectMemoryTotal;
    private $subjectLastMemoryInclusive;
    private $dom;

    public function __construct(
        CollectionBuilder $finder,
        SubjectBuilder $subjectBuilder,
        ProgressLogger $logger = null
    ) {
        $this->logger = $logger ?: new NullProgressLogger();
        $this->finder = $finder;
        $this->subjectBuilder = $subjectBuilder;
    }

    public function runAll()
    {
        $collection = $this->finder->buildCollection();

        $benchmarkResults = array();

        foreach ($collection->getBenchmarks() as $benchmark) {
            $this->logger->benchmarkStart($benchmark);
            $benchmarkResults[] = $this->run($benchmark);
            $this->logger->benchmarkEnd($benchmark);
        }

        $benchmarkSuiteResult = new SuiteResult($benchmarkResults);

        return $benchmarkSuiteResult;
    }

    private function run(Benchmark $benchmark)
    {
        $subjects = $this->subjectBuilder->buildSubjects($benchmark);
        $subjectResults = array();

        foreach ($subjects as $subject) {
            $this->logger->subjectStart($subject);
            $subjectResults[] = $this->runSubject($benchmark, $subject);
            $this->logger->subjectEnd($subject);
        }

        $benchmarkResult = new BenchmarkResult($benchmark, $subjectResults);

        return $benchmarkResult;
    }

    private function runSubject(Benchmark $benchmark, Subject $subject)
    {
        $this->subjectMemoryTotal = 0;
        $this->subjectLastMemoryInclusive = memory_get_usage();

        $paramProviderMethods = $subject->getParameterProviders();
        $parameterSets = array();

        foreach ($paramProviderMethods as $paramProviderMethod) {
            if (!method_exists($benchmark, $paramProviderMethod)) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Unknown param provider "%s" for bench benchmark "%s"',
                    $paramProviderMethod, get_class($benchmark)
                ));
            } $parameterSets[] = $benchmark->$paramProviderMethod();
        }

        if (!$parameterSets) {
            $parameterSets = array(array(array()));
        }

        $paramsIterator = new CartesianParameterIterator($parameterSets);

        $iterationsResults = array();
        foreach ($paramsIterator as $parameters) {
            $iterationResults = array();
            for ($index = 0; $index < $subject->getNbIterations(); $index++) {
                $iteration = new Iteration($index, $parameters);
                $iterationResults[] = $this->runIteration($benchmark, $subject, $iteration);
            }
            $iterationsResults[] = new IterationsResults($iterationResults);
        }

        $subjectResult = new SubjectResult($subject, $iterationsResults);

        return $subjectResult;
    }

    private function runIteration(Benchmark $benchmark, Subject $subject, Iteration $iteration)
    {
        foreach ($subject->getBeforeMethods() as $beforeMethodName) {
            if (!method_exists($benchmark, $beforeMethodName)) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Unknown bench benchmark method "%s"', $beforeMethodName
                ));
            }

            $benchmark->$beforeMethodName($iteration);
        }

        $startMemory = memory_get_usage();
        $start = microtime(true);
        $benchmark->{$subject->getMethodName()}($iteration);
        $end = microtime(true);
        $endMemory = memory_get_usage();

        $memoryDiff = $endMemory - $startMemory;
        $this->subjectMemoryTotal += $memoryDiff;
        $memoryDiffInclusive = $endMemory - $this->subjectLastMemoryInclusive;
        $this->subjectLastMemoryInclusive = $endMemory;

        $statistics['time'] = $end - $start;
        $statistics['subject_memory_total'] = $this->subjectMemoryTotal;
        $statistics['subject_memory_diff'] = $memoryDiff;
        $statistics['memory_inclusive'] = $endMemory;
        $statistics['memory_diff_inclusive'] = $memoryDiffInclusive;
        $iterationResult = new IterationResult($iteration, $statistics);

        return $iterationResult;
    }
}
