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
use PhpBench\Benchmark;
use PhpBench\Result\SubjectResult;
use PhpBench\Result\IterationResult;
use PhpBench\Result\BenchmarkResult;
use PhpBench\Result\SuiteResult;
use PhpBench\Result\IterationsResult;
use PhpBench\Exception\InvalidArgumentException;
use PhpBench\Result\Loader\XmlLoader;

class Runner
{
    const MILLION = 1000000;

    private $logger;
    private $finder;
    private $subjectBuilder;
    private $subjectMemoryTotal;
    private $subjectLastMemoryInclusive;
    private $separateProcess;

    public function __construct(
        CollectionBuilder $finder,
        SubjectBuilder $subjectBuilder,
        ProgressLogger $logger = null,
        $separateProcess = false
    ) {
        $this->logger = $logger ?: new NullProgressLogger();
        $this->finder = $finder;
        $this->subjectBuilder = $subjectBuilder;
        $this->separateProcess = $separateProcess;
    }

    public function runAll($noSetup = false, $parameters = null)
    {
        $collection = $this->finder->buildCollection();

        $benchmarkResults = array();

        foreach ($collection->getBenchmarks() as $benchmark) {
            $this->logger->benchmarkStart($benchmark);
            $benchmarkResults[] = $this->run($benchmark, $noSetup, $parameters);
            $this->logger->benchmarkEnd($benchmark);
        }

        $benchmarkSuiteResult = new SuiteResult($benchmarkResults);

        return $benchmarkSuiteResult;
    }

    private function run(Benchmark $benchmark, $noSetup, $parameters)
    {
        if (false === $noSetup && method_exists($benchmark, 'setUp')) {
            $benchmark->setUp();
        }

        $subjects = $this->subjectBuilder->buildSubjects($benchmark);
        $subjectResults = array();

        foreach ($subjects as $subject) {
            $this->logger->subjectStart($subject);
            $subjectResults[] = $this->runSubject($benchmark, $subject, $parameters);
            $this->logger->subjectEnd($subject);
        }

        if (false === $noSetup && method_exists($benchmark, 'tearDown')) {
            $benchmark->tearDown();
        }

        $benchmarkResult = new BenchmarkResult(get_class($benchmark), $subjectResults);

        return $benchmarkResult;
    }

    private function runSubject(Benchmark $benchmark, Subject $subject, $parameters)
    {
        $this->subjectMemoryTotal = 0;
        $this->subjectLastMemoryInclusive = memory_get_usage();


        if (null !== $parameters) {
            $parameterSets = array(array($parameters));
        } else {
            $parameterSets = $this->getParameterSets($benchmark, $subject);
        }

        if (!$parameterSets) {
            $parameterSets = array(array(array()));
        }

        $paramsIterator = new CartesianParameterIterator($parameterSets);

        $iterationsResults = array();
        foreach ($paramsIterator as $parameters) {
            if ($this->separateProcess) {
                $iterationsResults[] = $this->runIterationsSeparateProcess($benchmark, $subject, $parameters);
            } else {
                $iterationsResults[] = $this->runIterations($benchmark, $subject, $parameters);
            }
        }

        $subjectResult = new SubjectResult(
            $subject->getMethodName(),
            $subject->getDescription(),
            $iterationsResults
        );

        return $subjectResult;
    }

    private function runIterations(Benchmark $benchmark, Subject $subject, $parameters)
    {
        $iterationResults = array();
        for ($index = 0; $index < $subject->getNbIterations(); $index++) {
            $iteration = new Iteration($index, $parameters);
            $iterationResults[] = $this->runIteration($benchmark, $subject, $iteration);
        }
        return new IterationsResult($iterationResults, $parameters);
    }

    private function runIterationsSeparateProcess(Benchmark $benchmark, Subject $subject, $parameters)
    {
        $reflection = new \ReflectionClass(get_class($benchmark));

        $bin = realpath(__DIR__ . '/../..') . '/bin/phpbench';
        $command = sprintf(
            '%s run %s --subject=%s --nosetup --dump --parameters=\'%s\'',
            $bin,
            $reflection->getFileName(),
            $subject->getMethodName(),
            json_encode($parameters)
        );
        exec($command, $output, $exitCode);
        $output = implode(PHP_EOL, $output);

        if (0 !== $exitCode) {
            throw new \RuntimeException(sprintf(
                'Failed to execute: "%s": %s',
                $command,
                $output
            ));
        }

        $loader = new XmlLoader();
        $suiteResult = $loader->load($output);
        $iterationsResults = $suiteResult->getIterationsResults();
        $iterationsResult = reset($iterationsResults);

        return $iterationsResult;
    }

    private function runIteration(Benchmark $benchmark, Subject $subject, Iteration $iteration)
    {
        foreach ($subject->getBeforeMethods() as $beforeMethodName) {
            if (!method_exists($benchmark, $beforeMethodName)) {
                throw new InvalidArgumentException(sprintf(
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

        $statistics['time'] = ($end * self::MILLION) - ($start * self::MILLION);
        $statistics['memory'] = $this->subjectMemoryTotal;
        $statistics['memory_diff'] = $memoryDiff;
        $statistics['memory_inc'] = $endMemory;
        $statistics['memory_diff_inc'] = $memoryDiffInclusive;
        $iterationResult = new IterationResult($statistics);

        return $iterationResult;
    }

    private function getParameterSets(Benchmark $benchmark, Subject $subject)
    {
        $paramProviderMethods = $subject->getParameterProviders();
        $parameterSets = array();

        foreach ($paramProviderMethods as $paramProviderMethod) {
            if (!method_exists($benchmark, $paramProviderMethod)) {
                throw new InvalidArgumentException(sprintf(
                    'Unknown param provider "%s" for bench benchmark "%s"',
                    $paramProviderMethod, get_class($benchmark)
                ));
            }
            $parameterSets[] = $benchmark->$paramProviderMethod();
        }

        return $parameterSets;
    }
}
