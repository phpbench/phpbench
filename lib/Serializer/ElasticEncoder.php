<?php

namespace PhpBench\Serializer;

use PhpBench\Model\Suite;
use PhpBench\Environment\Information;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Subject;
use PhpBench\Model\Variant;
use PhpBench\Model\Iteration;

class ElasticEncoder
{
    public function aggregationsFromSuite(Suite $suite)
    {
        $documents = [];

        $suiteData = [
            'suite' => $suite->getUuid(),
            'contextName' => $suite->getContextName(),
            'date' => $suite->getDate()->format('c'),
            'timestamp' => $suite->getDate()->format('U'),
            'configPath' => $suite->getConfigPath(),
            'env' => array_map(function (Information $info) {
                return $info->toArray();
            }, $suite->getEnvInformations()),
        ];

        foreach ($suite->getBenchmarks() as $benchmark) {
            $benchmarkData = array_merge($suiteData, $this->encodeBenchmark($benchmark));
            foreach ($benchmark->getSubjects() as $subject) {
                $subjectData = array_merge($benchmarkData, $this->encodeSubject($subject));
                foreach ($subject->getVariants() as $variantIndex => $variant) {
                    $variantData = array_merge($subjectData, $this->encodeVariant($variant));
                    $documents[$this->subjectId($suite, $benchmark, $subject) . $variantIndex] = $variantData;
                }
            }
        }

        return $documents;
    }

    public function iterationsFromSuite(Suite $suite)
    {
        $documents = [];
        foreach ($suite->getBenchmarks() as $benchmark) {
            /** @var Subject $subject */
            foreach ($benchmark->getSubjects() as $subject) {
                foreach ($subject->getVariants() as $variantIndex => $variant) {
                    foreach ($variant->getIterations() as $iterationIndex => $iteration) {

                        $iterationData = $this->encodeIteration($iteration);
                        $id = implode(
                            '',
                            [
                                $this->subjectId($suite, $benchmark, $subject),
                                $variantIndex,
                                $iterationIndex
                            ]
                        );
                        $iterationData['iteration'] = $iterationIndex;
                        $iterationData['suite'] = $suite->getUuid();
                        $iterationData['variant'] = $variantIndex;
                        $iterationData['subject'] = $subject->getName();
                        $iterationData['class'] = $benchmark->getClass();

                        $documents[$id] = $iterationData;
                    }
                }
            }
        }

        return $documents;
    }

    private function encodeBenchmark(Benchmark $benchmark)
    {
        return [
            'class' => $benchmark->getClass(),
        ];
    }

    private function encodeSubject(Subject $subject)
    {
        return [
            'name' => $subject->getName(),
            'groups' => $subject->getGroups(),
            'sleep' => $subject->getSleep(),
            'retry_threshold' => $subject->getRetryThreshold(),
            'output_time_unit' => $subject->getOutputTimeUnit(),
            'output_time_precision' => $subject->getOutputTimePrecision(),
            'output_mode' => $subject->getOutputMode(),
            'index' => $subject->getIndex(),
        ];
    }

    private function encodeVariant(Variant $variant)
    {
        return [
            'parameters' => $variant->getParameterSet()->getArrayCopy(),
            'nb_iterations' => count($variant->getIterations()),
            'rejects' => $variant->getRejects(),
            'revolutions' => $variant->getRevolutions(),
            'warmup' => $variant->getWarmup(),
            'stats' => $variant->getStats()->getStats(),
        ];
    }

    private function encodeIteration(Iteration $iteration)
    {
        $encoded = [
            'index' => $iteration->getIndex(),
            'results' => [],
        ];

        foreach ($iteration->getResults() as $result) {
            $encoded['results'][$result->getKey()] = [
                'class' => get_class($result),
                'metrics' => $result->getMetrics(),
            ];
        }

        return $encoded;
    }

    private function flatten(array $result, $flatKeys = [], array $flattened = [])
    {
        foreach ($result as $key => $value) {
            $newKeys = $flatKeys;
            $newKeys[] = $key;
            $newKey = implode('.', $newKeys);

            if (false === is_array($value)) {
                $flattened[$newKey] = $value;
                continue;
            }

            $flattened = $this->flatten($value, $newKeys, $flattened);
        }

        return $flattened;
    }

    private function subjectId(Suite $suite, Benchmark $benchmark, Subject $subject)
    {
        return $suite->getUuid() . $benchmark->getClass() . $subject->getName();
    }

}
