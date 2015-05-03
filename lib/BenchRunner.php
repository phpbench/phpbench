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

class BenchRunner
{
    private $logger;
    private $reportGenerators;
    private $finder;
    private $subjectBuilder;

    public function __construct(
        BenchFinder $finder,
        BenchSubjectBuilder $subjectBuilder,
        BenchProgressLogger $logger,
        array $reportGenerators,
        BenchMatrixBuilder $matrixBuilder = null
    ) {
        $this->logger = $logger;
        $this->reportGenerators = $reportGenerators;
        $this->finder = $finder;
        $this->subjectBuilder = $subjectBuilder;
        $this->matrixBuilder = $matrixBuilder ?: new BenchMatrixBuilder();
    }

    public function runAll()
    {
        $caseCollection = $this->finder->buildCollection();

        $caseResults = array();
        foreach ($caseCollection->getCases() as $case) {
            $this->logger->caseStart($case);
            $caseResults[] = $this->run($case);
            $this->logger->caseEnd($case);
        }
        $caseCollectionResult = new BenchCaseCollectionResult($caseCollection, $caseResults);

        foreach ($this->reportGenerators as $reportGenerator) {
            $reportGenerator->generate($caseCollectionResult);
        }

        return $caseCollectionResult;
    }

    private function run(BenchCase $case)
    {
        $subjects = $this->subjectBuilder->buildSubjects($case);
        $results = array();

        foreach ($subjects as $subject) {
            $this->logger->subjectStart($subject);
            $results[] = $this->runSubject($case, $subject);
            $this->logger->subjectEnd($subject);
        }

        $caseResult = new BenchCaseResult($case, $results);

        return $caseResult;
    }

    private function runSubject(BenchCase $case, BenchSubject $subject)
    {
        $paramProviderMethods = $subject->getParamProviders();
        $parameterSets = array();

        foreach ($paramProviderMethods as $paramProviderMethod) {
            if (!method_exists($case, $paramProviderMethod)) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Unknown param provider "%s" for bench case "%s"',
                    $paramProviderMethod, get_class($case)
                ));
            }

            $parameterSets[] = $case->$paramProviderMethod();
        }

        if (!$parameterSets) {
            $parameterSets = array(array(array()));
        }

        $matrix = new BenchCartesianParamIterator($parameterSets);
        $iterations = array();

        foreach ($matrix as $parameters) {
            for ($index = 0; $index < $subject->getNbIterations(); $index++) {
                $iteration = new BenchIteration($index, $parameters);
                $this->runIteration($case, $subject, $iteration);
                $iterations[] = $iteration;
            }
        }

        $subjectResult = new BenchSubjectResult($subject, $iterations);

        return $subjectResult;
    }

    private function runIteration(BenchCase $case, BenchSubject $subject, BenchIteration $iteration)
    {
        foreach ($subject->getBeforeMethods() as $beforeMethodName) {
            if (!method_exists($case, $beforeMethodName)) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Unknown bench case method "%s"', $beforeMethodName
                ));
            }

            $case->$beforeMethodName($iteration);
        }

        $start = microtime(true);
        $case->{$subject->getMethodName()}($iteration);
        $end = microtime(true);

        $iteration->setTime($end - $start);
    }
}
