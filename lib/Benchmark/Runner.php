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
use PhpBench\Benchmark\SuiteDocument;
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
    private $iterationsOverride;
    private $revsOverride;
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
        $dom = new SuiteDocument();
        $suiteEl = $dom->createElement('suite');

        $collection = $this->collectionBuilder->buildCollection($path);

        foreach ($collection->getBenchmarks() as $benchmark) {
            $benchmarkEl = $dom->createElement('benchmark');
            $benchmarkEl->setAttribute('class', get_class($benchmark));

            $this->logger->benchmarkStart($benchmark);
            $this->run($benchmark, $benchmarkEl);
            $this->logger->benchmarkEnd($benchmark);

            $suiteEl->appendChild($benchmarkEl);
        }

        $dom->appendChild($suiteEl);

        return $dom;
    }

    private function run(BenchmarkInterface $benchmark, \DOMElement $benchmarkEl)
    {
        $subjects = $this->subjectBuilder->buildSubjects($benchmark, $this->subjectsOverride, $this->groups);

        foreach ($subjects as $subject) {
            $subjectEl = $benchmarkEl->ownerDocument->createElement('subject');
            $subjectEl->setAttribute('name', $subject->getMethodName());

            foreach ($subject->getGroups() as $group) {
                $groupEl = $benchmarkEl->ownerDocument->createElement('group');
                $groupEl->setAttribute('name', $group);
                $subjectEl->appendChild($groupEl);
            }

            $this->logger->subjectStart($subject);
            $this->runSubject($benchmark, $subject, $subjectEl);
            $this->logger->subjectEnd($subject);

            $benchmarkEl->appendChild($subjectEl);
        }
    }

    private function runSubject(BenchmarkInterface $benchmark, Subject $subject, \DOMElement $subjectEl)
    {
        $iterationCount = null === $this->iterationsOverride ? $subject->getNbIterations() : $this->iterationsOverride;
        $revolutionCounts = $this->revsOverride ? array($this->revsOverride) : $subject->getRevs();
        $parameterSets = $this->getParameterSets($benchmark, $subject->getParamProviders(), $this->parametersOverride);

        $paramsIterator = new CartesianParameterIterator($parameterSets);

        foreach ($paramsIterator as $parameters) {
            $variantEl = $subjectEl->ownerDocument->createElement('variant');
            foreach ($parameters as $name => $value) {
                $parameterEl = $subjectEl->ownerDocument->createElement('parameter');
                $parameterEl->setAttribute('name', $name);
                $parameterEl->setAttribute('value', $value);
                $variantEl->appendChild($parameterEl);
            }

            $subjectEl->appendChild($variantEl);
            $this->runIterations($benchmark, $subject, $iterationCount, $revolutionCounts, $parameters, $variantEl);
        }
    }

    private function runIterations(BenchmarkInterface $benchmark, Subject $subject, $iterationCount, array $revolutionCounts, array $parameterSet, \DOMElement $variantEl)
    {
        for ($index = 0; $index < $iterationCount; $index++ ) {
            foreach ($revolutionCounts as $revolutionCount) {
                $iterationEl = $variantEl->ownerDocument->createElement('iteration');
                $variantEl->appendChild($iterationEl);
                $iterationEl->setAttribute('index', $iterationCount);
                $iterationEl->setAttribute('revs', $revolutionCount);
                $this->runIteration($benchmark, $subject, $revolutionCount, $parameterSet, $iterationEl);
            }
        }
    }

    private function runIteration(BenchmarkInterface $benchmark, Subject $subject, $revolutionCount, $parameterSet, \DOMElement $iterationEl)
    {
        $result = $this->executor->execute(
            $benchmark,
            $subject->getMethodName(),
            $revolutionCount,
            $subject->getBeforeMethods(),
            $subject->getAfterMethods(),
            $parameterSet
        );

        $iterationEl->setAttribute('time', $result['time']);
        $iterationEl->setAttribute('memory', $result['memory']);
    }

    private function getParameterSets(BenchmarkInterface $benchmark, array $paramProviderMethods, $parameters)
    {
        if ($parameters) {
            return array(array($parameters));
        }

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

        if (!$parameterSets) {
            $parameterSets = array(array(array()));
        }

        return $parameterSets;
    }
}
