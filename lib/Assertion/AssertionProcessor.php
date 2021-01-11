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

use PhpBench\Assertion\Ast\MemoryValue;
use PhpBench\Assertion\Ast\PercentageValue;
use PhpBench\Assertion\Ast\TimeValue;
use PhpBench\Math\Statistics;
use PhpBench\Model\Iteration;
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

    /**
     * @return array<string, mixed>
     */
    private function buildVariantData(Variant $variant): array
    {
        $stats = $variant->getStats();
        $timeUnit = $variant->getSubject()->getOutputTimeUnit();

        return [
            'min' => new TimeValue($stats->getMin(), $timeUnit),
            'max' => new TimeValue($stats->getMax(), $timeUnit),
            'stdev' => new TimeValue($stats->getStdev(), $timeUnit),
            'mean' => new TimeValue($stats->getMean(), $timeUnit),
            'mode' => new TimeValue($stats->getMode(), $timeUnit),
            'variance' => new TimeValue($stats->getVariance(), $timeUnit),
            'rstdev' => new PercentageValue($stats->getRstdev()),
            'mem_real' => new MemoryValue(Statistics::mean($variant->getMetricValues(MemoryResult::class, 'real'))),
            'mem_final' => new MemoryValue(Statistics::mean($variant->getMetricValues(MemoryResult::class, 'final'))),
            'mem_peak' => new MemoryValue(Statistics::mean($variant->getMetricValues(MemoryResult::class, 'peak'))),
        ];
    }
}
