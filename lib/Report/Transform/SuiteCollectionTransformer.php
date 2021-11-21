<?php

namespace PhpBench\Report\Transform;

use Generator;
use PhpBench\Data\DataFrame;
use PhpBench\Model\Iteration;
use PhpBench\Model\Subject;
use PhpBench\Model\Suite;
use PhpBench\Model\SuiteCollection;
use PhpBench\Model\Variant;

final class SuiteCollectionTransformer
{
    public const COL_HAS_BASELINE = 'has_baseline';

    public function suiteToFrame(SuiteCollection $collection, bool $includeBaseline = false): DataFrame
    {
        return DataFrame::fromRecords($this->suiteToTable($collection, $includeBaseline));
    }

    /**
     * @deprecated will be removed in 2.0, use `suiteToFrame`.
     */
    public function suiteToTable(SuiteCollection $collection, bool $includeBaseline = false): array
    {
        return $this->normalize(iterator_to_array($this->reportData($collection, $includeBaseline)));
    }

    /**
     * @return Generator<array<string, mixed>>
     */
    private function reportData(SuiteCollection $collection, bool $includeBaseline): Generator
    {
        foreach ($collection as $suite) {
            assert($suite instanceof Suite);

            foreach ($suite->getSubjects() as $subject) {
                foreach ($subject->getVariants() as $variantIndex => $variant) {
                    $nbIterations = (function (Variant $variant, ?Variant $baseline) {
                        if (null === $baseline) {
                            return count($variant->getIterations());
                        }

                        return max(count($variant->getIterations()), count($baseline->getIterations()));
                    })($variant, $variant->getBaseline());

                    for ($itNum = 0; $itNum < $nbIterations; $itNum++) {
                        $iteration = $variant->getIteration($itNum);
                        $baseline = $variant->getBaseline();
                        $baselineIteration = $baseline ? $baseline->getIteration($itNum) : null;

                        if ($includeBaseline && $baseline) {
                            yield array_merge(
                                [
                                    self::COL_HAS_BASELINE => false
                                ],
                                $this->createRow($baseline->getSubject(), $baseline, $baseline->getSubject()->getBenchmark()->getSuite(), $itNum, $variantIndex),
                                $this->resultData($baselineIteration, 'result')
                            );
                        }

                        yield array_merge(
                            [
                                self::COL_HAS_BASELINE => $baseline ? true : false
                            ],
                            $this->createRow($subject, $variant, $suite, $itNum, $variantIndex),
                            $this->resultData($iteration, 'result'),
                            $this->resultData($baselineIteration, 'baseline')
                        );
                    }
                }
            }
        }
    }

    /**
     * @return array<string,mixed>
     */
    private function resultData(?Iteration $iteration, string $prefix = 'result'): array
    {
        if (null === $iteration) {
            return [];
        }

        $data = [];

        foreach ($iteration->getResults() as $result) {
            foreach ($result->getMetrics() as $key => $value) {
                $data[sprintf('%s_%s_%s', $prefix, $result->getKey(), $key)] = $value;
            }
        }

        return $data;
    }


    /**
     * @param array<int,array<string,mixed>> $table
     *
     * @return array<int,array<string,mixed>>
     */
    private function normalize(array $table): array
    {
        $cols = [];

        foreach ($table as $row) {
            foreach ($row as $key => $value) {
                if (!isset($cols[$key])) {
                    $cols[$key] = null;
                }
            }
        }

        foreach ($table as &$row) {
            $row = array_merge($cols, $row);
        }

        return $table;
    }

    /**
     * @return array<string,mixed>
     */
    private function createRow(Subject $subject, Variant $variant, Suite $suite, int $itNum, int $variantIndex): array
    {
        return array_merge([
            'benchmark_name' => $subject->getBenchmark()->getName(),
            'benchmark_class' => $subject->getBenchmark()->getClass(),
            'subject_name' => $subject->getName(),
            'subject_groups' => $subject->getGroups(),
            'subject_time_unit' => $subject->getOutputTimeUnit(),
            'subject_time_precision' => $subject->getOutputTimePrecision(),
            'subject_time_mode' => $subject->getOutputMode(),
            'variant_index' => $variantIndex,
            'variant_name' => $variant->getParameterSet()->getName(),
            'variant_params' => $variant->getParameterSet()->toUnserializedParameters(),
            'variant_revs' => $variant->getRevolutions(),
            'variant_iterations' => count($variant->getIterations()),
            'suite_tag' => $suite->getTag() ? $suite->getTag()->__toString() : '<current>',
            'suite_date' => $suite->getDate()->format('Y-m-d'),
            'suite_time' => $suite->getDate()->format('H:i:s'),
            'iteration_index' => $itNum,
        ], $this->envData($suite));
    }

    /**
     * @return array<string,mixed>
     */
    private function envData(Suite $suite): array
    {
        $data = [];

        foreach ($suite->getEnvInformations() as $info) {
            $name = $info->getName();

            foreach ($info as $key => $value) {
                $data[sprintf('env_%s_%s', $name, $key)] = $value;
            }
        }

        return $data;
    }
}
