<?php

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
    )
    {
        $this->logger = $logger;
        $this->reportGenerators = $reportGenerators;
        $this->finder = $finder;
        $this->subjectBuilder = $subjectBuilder;
        $this->matrixBuilder = $matrixBuilder ? : new BenchMatrixBuilder();
    }

    public function runAll()
    {
        $caseCollection = $this->finder->buildCollection();

        foreach ($caseCollection->getCases() as $case) {
            $this->logger->caseStart($case);
            $this->run($case);
            $this->logger->caseEnd($case);
        }

        foreach ($this->reportGenerators as $reportGenerator) {
            $reportGenerator->generate($caseCollection);
        }
    }

    private function run(BenchCase $case) 
    {
        $subjects = $this->subjectBuilder->buildSubjects($case);

        foreach ($subjects as $subject) {
            $this->logger->subjectStart($subject);
            $this->runSubject($case, $subject);
            $this->logger->subjectEnd($subject);
        }
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

        $matrix = $this->matrixBuilder->buildMatrix($parameterSets);

        foreach ($matrix as $parameters) {
            for ($index = 0; $index < $subject->getNbIterations(); $index++) {
                $iteration = new BenchIteration($index, $parameters);
                $this->runIteration($case, $subject, $iteration);
                $subject->addIteration($iteration);
            }
        }
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

    private function buildMatrix($bench, BenchCase $case)
    {
        throw new \Exception('TODO');
    }
}
