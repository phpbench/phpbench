<?php

namespace PhpBench\Expression\NodeEvaluator;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NumberNode;
use PhpBench\Expression\Ast\PhpValueFactory;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Ast\ValueWithUnitNode;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Exception\EvaluationError;
use PhpBench\Expression\NodeEvaluator;
use PhpBench\Util\MemoryUnit;
use PhpBench\Util\TimeUnit;

class ValueWithUnitEvaluator implements NodeEvaluator
{
    /**
        * @param parameters $params
     */
    public function evaluate(Evaluator $evaluator, Node $node, array $params): ?Node
    {
        if (!$node instanceof ValueWithUnitNode) {
            return null;
        }

        $value = $evaluator->evaluateType($node->left(), NumberNode::class, $params);
        $unit = $evaluator->evaluateType($node->unit()->unit(), StringNode::class, $params)->value();

        if (TimeUnit::isTimeUnit($unit)) {
            return PhpValueFactory::fromValue($this->timeUnit($value->value(), $unit));
        }

        if (MemoryUnit::isMemoryUnit($unit)) {
            return PhpValueFactory::fromValue($this->memoryUnit($value->value(), $unit));
        }

        throw new EvaluationError($node, sprintf(
            'Do not know how to evaluate unit "%s"',
            $unit
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
