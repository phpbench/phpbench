<?php

namespace PhpBench\Assertion;

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
                'variant' => $variantData,
                'baseline' => $variant->getBaseline() ? $this->buildVariantData($variant->getBaseline()) : $variantData,
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
}
