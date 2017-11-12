<?php

namespace PhpBench\Serializer;

use PhpBench\Model\Suite;
use PhpBench\Environment\Information;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Subject;
use PhpBench\Model\Variant;
use PhpBench\Model\Iteration;

class ArrayEncoder
{
    public function encodeSuite(Suite $suite)
    {
        $result = [
            'contextName' => $suite->getContextName(),
            'date' => $suite->getDate()->format('c'),
            'configPath' => $suite->getConfigPath(),
            'envInformations' => array_map(function (Information $info) {
                return $info->toArray();
            }, $suite->getEnvInformations()),
            'benchmarks' => array_map(function (Benchmark $benchmark) {
                return $this->encodeBenchmark($benchmark);
            }, $suite->getBenchmarks()),
        ];

        return $this->flatten($result);
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

    private function encodeBenchmark(Benchmark $benchmark)
    {
        return [
            'class' => $benchmark->getClass(),
            'subjects' => array_map(function (Subject $subject) {
                return $this->encodeSubject($subject);
            }, $benchmark->getSubjects()),
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
            'variants' => array_map(function (Variant $variant) {
                return $this->encodeVariant($variant);
            }, $subject->getVariants()),
        ];
    }

    private function encodeVariant(Variant $variant)
    {
        return [
            'parameters' => $variant->getParameterSet()->getArrayCopy(),
            'iterations' => $variant->getIterations(),
            'rejects' => $variant->getRejects(),
            'revolutions' => $variant->getRevolutions(),
            'warmup' => $variant->getWarmup(),
            'iterations' => array_map(function (Iteration $iteration) {
                return $this->encodeIteration($iteration);
            }, $variant->getIterations()),
            'stats' => $variant->getStats()->getStats(),
        ];
    }

    private function encodeIteration(Iteration $iteration)
    {
        $encoded = [
            'index' => $iteration->getIndex(),
        ];

        foreach ($iteration->getResults() as $result) {
            $encoded[$result->getKey()] = $result->getMetrics();
        }

        return $encoded;
    }
}
