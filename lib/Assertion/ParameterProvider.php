<?php

namespace PhpBench\Assertion;

use PhpBench\Expression\NodePrinter\DisplayAsPrinter;
use PhpBench\Model\Subject;
use PhpBench\Model\Variant;

class ParameterProvider
{
    /**
     * @return array<string,mixed>
     */
    public function provideFor(Variant $variant)
    {
        return (function (array $variantData) use ($variant) {
            return [
                'subject' => $this->subjectData($variant->getSubject()),
                'variant' => $variantData,
                'baseline' => $variant->getBaseline() ? $this->buildVariantData($variant->getBaseline()) : $variantData,
                DisplayAsPrinter::PARAM_OUTPUT_TIME_UNIT => $variant->getSubject()->getOutputTimeUnit(),
                DisplayAsPrinter::PARAM_OUTPUT_TIME_PRECISION => $variant->getSubject()->getOutputTimePrecision(),
            ];
        })($this->buildVariantData($variant));
    }

    /**
     * @return array<string, array<string, array<int,mixed>>>
     */
    private function buildVariantData(Variant $variant)
    {
        $data = [];

        foreach ($variant->getIterations() as $iteration) {
            foreach ($iteration->getResults() as $result) {
                $metrics = $result->getMetrics();
                $resultKey = (string)$result->getKey();

                foreach ($metrics as $name => $value) {
                    $name = (string)$name;

                    if (!isset($data[$resultKey][$name])) {
                        $data[$resultKey][$name] = [];
                    }
                    $data[$resultKey][(string)$name][] = $value;
                }
            }
        }

        return $data;
    }

    /**
     * @return array<string, int|string|null>
     */
    private function subjectData(Subject $subject): array
    {
        return [
            'time_unit' => $subject->getOutputTimeUnit(),
            'time_precision' => $subject->getOutputTimePrecision(),
            'time_mode' => $subject->getOutputMode(),
        ];
    }
}
