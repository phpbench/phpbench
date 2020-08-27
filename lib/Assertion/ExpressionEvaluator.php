<?php

namespace PhpBench\Assertion;

use PhpBench\Assertion\Ast\Comparison;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Ast\PercentageValue;
use PhpBench\Assertion\Ast\PropertyAccess;
use PhpBench\Assertion\Ast\TimeValue;
use PhpBench\Assertion\Ast\WithinRangeOf;
use PhpBench\Assertion\Exception\ExpressionEvaluatorError;
use PhpBench\Math\FloatNumber;
use PhpBench\Math\Statistics;
use PhpBench\Util\TimeUnit;

class ExpressionEvaluator
{
    /**
     * @var array<string,mixed>
     */
    private $args;

    /**
     * @param array<string,mixed>
     */
    public function __construct(array $args = [])
    {
        $this->args = $args;
    }

    /**
     * @return mixed
     */
    public function evaluate(Node $node)
    {
        if ($node instanceof Comparison) {
            return $this->evaluateComparison($node);
        }

        if ($node instanceof TimeValue) {
            return $this->evaluateTimeValue($node);
        }

        if ($node instanceof PropertyAccess) {
            return $this->evaluatePropertyAccess($node);
        }

        if ($node instanceof WithinRangeOf) {
            return $this->evaluateWithinRangeOf($node);
        }

        throw new ExpressionEvaluatorError(sprintf(
            'Do not know how to evaluate node "%s"',
            get_class($node)
        ));
    }

    /**
     * @return mixed
     */
    private function evaluateComparison(Comparison $node)
    {
        $left = $this->evaluate($node->value1());
        $right = $this->evaluate($node->value2());

        switch ($node->operator()) {
            case 'less than':
                return $left < $right;
        }

        throw new ExpressionEvaluatorError(sprintf(
            'Do not know how to compare operator "%s"',
            $node->operator()
        ));
    }

    /**
     * @return mixed
     */
    private function evaluateTimeValue(TimeValue $node)
    {
        return TimeUnit::convert($node->value(), $node->unit(), TimeUnit::MICROSECONDS, TimeUnit::MODE_TIME);
    }

    /**
     * @return mixed
     */
    private function evaluatePropertyAccess(PropertyAccess $node)
    {
        return PropertyAccess::resolvePropertyAccess($node->segments(), $this->args);
    }

    /**
     * @return mixed
     */
    private function evaluateWithinRangeOf(WithinRangeOf $node)
    {
        $value1 = $this->evaluate($node->value1());
        $value2 = $this->evaluate($node->value2());

        $range = $node->range();
        if ($range instanceof PercentageValue) {
            return FloatNumber::isLessThanOrEqual(
                Statistics::percentageDifference($value1, $value2),
                $range->percentage()
            );
        }

        return FloatNumber::isLessThanOrEqual(
            abs($value2 - $value1),
            $this->evaluate($range)
        );
    }
}
