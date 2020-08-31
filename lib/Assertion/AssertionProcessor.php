<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Assertion;

use PhpBench\Math\Statistics;
use PhpBench\Model\Result\MemoryResult;
use PhpBench\Model\Variant;

class AssertionProcessor
{
    /**
     * @var ExpressionEvaluatorFactory
     */
    private $evaluator;

    /**
     * @var ExpressionParser
     */
    private $parser;

    public function __construct(ExpressionParser $parser, ExpressionEvaluatorFactory $evaluator)
    {
        $this->evaluator = $evaluator;
        $this->parser = $parser;
    }

    public function assert(Variant $variant, string $assertion): AssertionResult
    {
        $variantData = $this->buildVariantData($variant);
        $result = $this->evaluator->createWithArgs([
            'variant' => $variantData,
            'baseline' => $variant->getBaseline() ? $this->buildVariantData($variant->getBaseline()) : $variantData,
        ])->evaluate($this->parser->parse($assertion));

        return $result;
    }

    private function buildVariantData(Variant $variant): array
    {
        return array_merge($variant->getStats()->getStats(), [
            'mem_real' => Statistics::mean($variant->getMetricValues(MemoryResult::class, 'real')),
            'mem_final' => Statistics::mean($variant->getMetricValues(MemoryResult::class, 'final')),
            'mem_peak' => Statistics::mean($variant->getMetricValues(MemoryResult::class, 'peak')),
        ]);
    }
}
