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
    private $processIsolation;
    private $iterationsOverride;
    private $revsOverride;
    private $setUpTearDown;
    private $configFile;

    /**
     * @param CollectionBuilder $finder
     * @param SubjectBuilder $subjectBuilder
     * @param ProgressLogger $logger
     * @param mixed $processIsolation ProcessIsolation override
     * @param mixed $setUpTearDown Enable or disable setUp and tearDown
     * @param mixed $iterationsOverride Override the number of iterations
     * @param mixed $configFile Isolated proceses need to know about the config
     */
    public function __construct(
        CollectionBuilder $finder,
        SubjectBuilder $subjectBuilder,
        ProgressLogger $logger = null,
        $processIsolation = null,
        $setUpTearDown = true,
        $iterationsOverride = null,
        $revsOverride = null,
        $configFile = null
    ) {
        $this->logger = $logger ?: new NullProgressLogger();
        $this->finder = $finder;
        $this->subjectBuilder = $subjectBuilder;
        $this->processIsolation = $processIsolation;
        $this->setUpTearDown = $setUpTearDown;
        $this->iterationsOverride = $iterationsOverride;
        $this->revsOverride = $revsOverride;
        $this->configFile = $configFile;
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
        if (true === $this->setUpTearDown && method_exists($benchmark, 'setUp')) {
            $benchmark->setUp();
        }

        $subjects = $this->subjectBuilder->buildSubjects($benchmark);
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

        if ($this->configFile) {
            $command .= ' --config=' . $this->configFile;
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
