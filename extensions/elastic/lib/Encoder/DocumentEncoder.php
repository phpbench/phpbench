<?php

namespace PhpBench\Extensions\Elastic\Encoder;

use PhpBench\Model\Suite;
use PhpBench\Environment\Information;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Subject;
use PhpBench\Model\Variant;
use PhpBench\Model\Iteration;

class DocumentEncoder
{
    public function documentsFromSuite(Suite $suite)
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
                    $documents[$suite->getUuid() . $benchmark->getClass() . $subject->getName() . $variantIndex] = $variantData;
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
            'iterations' => array_map(function (Iteration $iteration) {
                return $this->encodeIteration($iteration);
            }, $variant->getIterations()),
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

}
