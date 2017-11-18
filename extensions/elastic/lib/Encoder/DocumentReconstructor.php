<?php

namespace PhpBench\Extensions\Elastic\Encoder;

use PhpBench\Model\Variant;
use PhpBench\Model\Subject;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Suite;
use DateTime;
use PhpBench\Model\ParameterSet;
use PhpBench\Environment\Information;

class DocumentReconstructor
{
    private $suites = [];
    private $benchmarks = [];
    private $subjects = [];

    public function __construct(array $documents)
    {
        foreach ($documents as $variant) {
            $this->processVariant($variant);
        }
    }

    private function processVariant(array $variant)
    {
        $suite = $this->suiteFrom($variant);
        $benchmark = $this->benchmarkFrom($suite, $variant);
        $subject = $this->subjectFrom($benchmark, $variant);
        $this->variantFrom($subject, $variant);
    }

    private function suiteFrom(array $variant): Suite
    {
        $suiteId = $variant['suite'];
        if (isset($this->suites[$suiteId])) {
            return $this->suites[$suiteId];
        }


        $suite = new Suite(
            $variant['contextName'],
            new DateTime($variant['date']),
            $variant['configPath'],
            [],
            [],
            $variant['suite']
        );

        foreach ($variant['env'] as $envName => $envInformation) {
            $suite->addEnvInformation(new Information($envName, $envInformation));
        }

        $this->suites[$suiteId] = $suite;

        return $suite;
    }

    public function getSuite(): Suite
    {
        foreach ($this->suites as $suite) {
            return $suite;
        }
    }

    private function benchmarkFrom(Suite $suite, array $variant): Benchmark
    {
        $class = $variant['class'];
        if (isset($this->benchmarks[$class])) {
            return $this->benchmarks[$class];
        }

        $this->benchmarks[$class] = $suite->createBenchmark($class);;

        return $this->benchmarkFrom($suite, $variant);
    }

    private function subjectFrom(Benchmark $benchmark, array $variant): Subject
    {
        $name = $variant['name'];
        if (isset($this->subjects[$name])) {
            return $this->subjects[$name];
        }

        $subject = $benchmark->createSubject($name);
        $subject->setGroups($variant['groups']);
        $subject->setSleep($variant['sleep']);
        $this->subjects[$name] = $subject;

        return $this->subjectFrom($benchmark, $variant);

    }

    private function variantFrom(Subject $subject, array $variant)
    {
        $parameterSet = new ParameterSet(0, $variant['parameters']);
        $variantModel = $subject->createVariant($parameterSet, $variant['revolutions'], $variant['warmup'], $variant['stats']);

        foreach ($variant['iterations'] as $iteration) {
            $iterationModel = $variantModel->createIteration();
            foreach ($iteration['results'] as $result) {
                $iterationModel->setResult(call_user_func_array([
                    $result['class'],
                    'fromArray',
                ], [ $result['metrics'] ]));
            }
        }
    }
}
