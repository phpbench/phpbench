<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench;

use PhpBench\ProgressLogger\NullProgressLogger;

class Runner
{
    private $logger;
    private $finder;
    private $subjectBuilder;
    private $subjectMemoryTotal;
    private $subjectLastMemoryInclusive;
    private $resultBuilder;
    private $dom;

    public function __construct(
        Finder $finder,
        SubjectBuilder $subjectBuilder,
        ProgressLogger $logger = null,
        ResultBuilder $resultBuilder = null
    ) {
        $this->logger = $logger ?: new NullProgressLogger();
        $this->finder = $finder;
        $this->subjectBuilder = $subjectBuilder;
        $this->dom = new \DOMDocument('1.0');
    }

    public function runAll()
    {
        $collection = $this->finder->buildCollection();

        $phpbenchEl = $this->dom->createElement('phpbench');
        $phpbenchEl->setAttribute('version', PhpBench::VERSION);
        $phpbenchEl->setAttribute('date', date('Y-m-d H:i:s'));
        $this->dom->addChild($phpbenchEl);

        foreach ($collection->getCases() as $benchmark) {
            $this->logger->benchmarkStart($benchmark);
            $benchmarkEl = $this->run($benchmark);
            $this->logger->benchmarkEnd($benchmark);
            $phpbenchEl->addChild($benchmarkEl);
        }

        return $this->dom;
    }

    private function run(Benchmark $benchmark)
    {
        $benchmarkEl = $this->dom->createElement('benchmark');
        $benchmarkEl->setAttribute('class', get_class($benchmark));

        $subjects = $this->subjectBuilder->buildSubjects($benchmark);

        foreach ($subjects as $subject) {
            $this->logger->subjectStart($subject);
            $subjectEl = $this->runSubject($benchmark, $subject);
            $this->logger->subjectEnd($subject);
            $benchmarkEl->addChild($subjectEl);
        }

        return $benchmarkEl;
    }

    private function runSubject(Benchmark $benchmark, Subject $subject)
    {
        $subjectEl = $this->dom->createElement('subject');
        $subjectEl->setAttribute('method', $subject->getMethodName());
        $subjectEl->setAttribute('description', $subject->getDescription());

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

        foreach ($paramsIterator as $parameters) {
            for ($index = 0; $index < $subject->getNbIterations(); $index++) {
                $iteration = new Iteration($index, $parameters);
                $iterationEl = $this->runIteration($benchmark, $subject, $iteration);
                $subjectEl->appendChild($iterationEl);
            }
        }

        return $subjectEl;
    }

    private function runIteration(BenchCase $benchmark, BenchSubject $subject, BenchIteration $iteration)
    {
        $iterationEl = $this->dom->createElement('iteration');

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

        $iterationEl->setAttribute('time', $end - $start);
        $iterationEl->setAttribute('subject-memory-total', $this->subjectMemoryTotal);
        $iterationEl->setAttribute('subject-memory-diff', $memoryDiff);
        $iterationEl->setAttribute('memory-inclusive', $endMemory);
        $iterationEl->setAttribute('memory-diff-inclusive', $memoryDiffInclusive);

        return $iterationEl;
    }
}
