<?php

namespace PhpBench\Expression\Evaluator;

use PhpBench\Assertion\Exception\ExpressionEvaluatorError;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NumberNode;
use PhpBench\Expression\Ast\NumberNodeFactory;
use PhpBench\Expression\Ast\UnitNode;
use PhpBench\Expression\Evaluator;
use PhpBench\Util\MemoryUnit;
use PhpBench\Util\TimeUnit;

/**
 * @extends AbstractEvaluator<UnitNode>
 */
class UnitEvaluator extends AbstractEvaluator
{
    final public function __construct()
    {
        parent::__construct(UnitNode::class);
    }

    public function evaluate(Evaluator $evaluator, Node $node): Node
    {
        $value = $evaluator->evaluateType($node->left(), NumberNode::class);
        $unit = $node->unit();

        if (TimeUnit::isTimeUnit($unit)) {
            return NumberNodeFactory::fromNumber($this->timeUnit($value->value(), $unit));
        }

        if (MemoryUnit::isMemoryUnit($unit)) {
            return NumberNodeFactory::fromNumber($this->memoryUnit($value->value(), $unit));
        }

        throw new ExpressionEvaluatorError(sprintf(
            'Do not know how to evaluate unit "%s"', $unit
        ));
    }

    /**
     * Return time in microseconds
     */
    private function timeUnit(float $value, string $unit): float
    {
        return TimeUnit::convert($value, $unit, TimeUnit::MICROSECONDS, TimeUnit::MODE_TIME);
    }

    /**
     * Return memory in bytes
     */
    private function memoryUnit(float $value, string $unit): float
    {
        return MemoryUnit::convertTo($value, $unit, MemoryUnit::BYTES);
    }
}
