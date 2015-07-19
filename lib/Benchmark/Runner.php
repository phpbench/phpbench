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
    private $collectionBuilder;
    private $subjectBuilder;
    private $subjectMemoryTotal;
    private $subjectLastMemoryInclusive;
    private $processIsolation;
    private $iterationsOverride;
    private $revsOverride;
    private $setUpTearDown;
    private $configPath;
    private $parametersOverride;
    private $subjectsOverride;
    private $groups;

    /**
     * @param CollectionBuilder $collectionBuilder
     * @param SubjectBuilder $subjectBuilder
     * @param mixed $configPath
     */
    public function __construct(
        CollectionBuilder $collectionBuilder,
        SubjectBuilder $subjectBuilder,
        $configPath
    ) {
        $this->logger = new NullProgressLogger();
        $this->collectionBuilder = $collectionBuilder;
        $this->subjectBuilder = $subjectBuilder;
        $this->configPath = $configPath;
    }

    public function overrideSubjects($subjects)
    {
        $this->subjectsOverride = $subjects;
    }

    public function setProgressLogger(ProgressLogger $logger)
    {
        $this->logger = $logger;
    }

    public function setProcessIsolation($processIsolation)
    {
        $this->processIsolation = $processIsolation;
    }

    public function disableSetup()
    {
        $this->setUpTearDown = false;
    }

    public function overrideIterations($iterations)
    {
        $this->iterationsOverride = $iterations;
    }

    public function overrideRevs($revs)
    {
        $this->revsOverride = $revs;
    }

    public function overrideParameters($parameters)
    {
        $this->parametersOverride = $parameters;
    }

    public function setGroups(array $groups)
    {
        $this->groups = $groups;
    }

    public function setConfigPath($configPath)
    {
        $this->configPath = $configPath;
    }
    

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

    private function run(Benchmark $benchmark)
    {
        if (true === $this->setUpTearDown && method_exists($benchmark, 'setUp')) {
            $benchmark->setUp();
        }

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

    private function runSubject(Benchmark $benchmark, Subject $subject)
    {
        $this->subjectMemoryTotal = 0;
        $this->subjectLastMemoryInclusive = memory_get_usage();
        $nbIterations = null === $this->iterationsOverride ? $subject->getNbIterations() : $this->iterationsOverride;
        $processIsolation = null !== $this->processIsolation ? $this->processIsolation : $subject->getProcessIsolation();

        $revs = $this->revsOverride ? array($this->revsOverride) : $subject->getRevs();

        $iterationsResults = array();

        if (false !== $processIsolation) {
            $iterationsResults[] = $this->runIterationsSeparateProcess($benchmark, $subject, $nbIterations, $processIsolation, $revs);
        } else {
            $iterationsResults[] = $this->runIterations($benchmark, $subject, $nbIterations, $revs);
        }

        $subjectResult = new SubjectResult(
            $subject->getIdentifier(),
            $subject->getMethodName(),
            $subject->getDescription(),
            $subject->getGroups(),
            $subject->getParameters(),
            $iterationsResults
        );

        return $subjectResult;
    }

    private function runIterations(Benchmark $benchmark, Subject $subject, $nbIterations, $revs)
    {
        $iterationResults = array();
        for ($index = 0; $index < $nbIterations; $index++) {
            foreach ($revs as $nbRevs) {
                $iteration = new Iteration($index, $subject->getParameters(), $nbRevs);
                $iterationResults[] = $this->runIteration($benchmark, $subject, $iteration);
            }
        }

        return new IterationsResult($iterationResults);
    }

    private function runIterationsSeparateProcess(Benchmark $benchmark, Subject $subject, $nbIterations, $processIsolation, $revs)
    {
        switch ($processIsolation) {
            case 'iteration':
                $iterationCount = $nbIterations;
                $nbIterations = 1;
                break;
            case 'iterations':
                $iterationCount = 1;
                break;
            default:
                throw new \RuntimeException(sprintf(
                    'Invalid process islation policy "%s". This really should not happen.',
                    $processIsolation
                ));
        }

        $iterationsResult = array();

        foreach ($revs as $nbRevs) {
            for ($index = 0; $index < $iterationCount; $index++) {
                $subIterationsResult = $this->runIterationSeparateProcess($benchmark, $subject, $nbIterations, $nbRevs);

                foreach ($subIterationsResult->getIterationResults() as $subIterationResult) {
                    $iterationsResult[] = $subIterationResult;
                }
            }
        }

        return new IterationsResult($iterationsResult, $subject->getParameters());
    }

    private function runIterationSeparateProcess(Benchmark $benchmark, Subject $subject, $nbIterations, $nbRevs)
    {
        $reflection = new \ReflectionClass(get_class($benchmark));
        $bin = realpath(__DIR__ . '/../..') . '/bin/phpbench';

        $command = sprintf(
            'php %s run %s --subject=%s --no-setup --dump --parameters=%s --iterations=%d --process-isolation=none',
            $bin,
            $reflection->getFileName(),
            $subject->getMethodName(),
            escapeshellarg(json_encode($subject->getParameters())),
            $nbIterations
        );

        if ($this->configPath) {
            $command .= ' --config=' . $this->configPath;
        }

        if ($nbRevs) {
            $command .= ' --revs=' . $nbRevs;
        }

        $descriptors = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );
        $process = proc_open($command, $descriptors, $pipes);
        fclose($pipes[0]);

        if (!is_resource($process)) {
            throw new \RuntimeException(
                'Could not spawn isolated process'
            );
        }

        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $exitCode = proc_close($process);

        if (0 !== $exitCode) {
            throw new \RuntimeException(sprintf(
                'Isolated process returned exit code "%s". Command: "%s". stdout: %s stderr: %s',
                $exitCode,
                $command,
                $output,
                $stderr
            ));
        }

        $loader = new XmlLoader();
        $subSuiteResult = $loader->load($output);
        $subIterationsResults = $subSuiteResult->getIterationsResults();
        $subIterationsResult = reset($subIterationsResults);

        return $subIterationsResult;
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
        for ($revolution = 0; $revolution < $iteration->getRevs(); $revolution++) {
            $benchmark->{$subject->getMethodName()}($iteration, $revolution);
        }
        $end = microtime(true);
        $endMemory = memory_get_usage();

        $memoryDiff = $endMemory - $startMemory;
        $this->subjectMemoryTotal += $memoryDiff;
        $this->subjectLastMemoryInclusive = $endMemory;

        $statistics['index'] = $iteration->getIndex();
        $statistics['revs'] = $iteration->getRevs();
        $statistics['time'] = ($end * self::MILLION) - ($start * self::MILLION);
        $statistics['memory'] = $this->subjectMemoryTotal;
        $statistics['memory_diff'] = $memoryDiff;
        $statistics['pid'] = getmypid();
        $iterationResult = new IterationResult($statistics);

        return $iterationResult;
    }

    public static function validateProcessIsolation($processIsolation)
    {
        $isolationPolicies = array(false, 'iteration', 'iterations');
        if (in_array($processIsolation, $isolationPolicies)) {
            return;
        }

        array_shift($isolationPolicies);

        throw new InvalidArgumentException(sprintf(
            'Process isolation must be one of "%s"',
            implode('", "', $isolationPolicies)
        ));
    }
}
