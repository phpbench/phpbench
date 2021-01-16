<?php

namespace PhpBench\Assertion;

use PhpBench\Assertion\Ast\Assertion;
use PhpBench\Assertion\Ast\Comparison;
use PhpBench\Assertion\Ast\FloatNode;
use PhpBench\Assertion\Ast\IntegerNode;
use PhpBench\Assertion\Ast\MemoryValue;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Ast\PercentageValue;
use PhpBench\Assertion\Ast\PropertyAccess;
use PhpBench\Assertion\Ast\ThroughputValue;
use PhpBench\Assertion\Ast\TimeValue;
use PhpBench\Assertion\Ast\Value;
use PhpBench\Assertion\Ast\WithinRangeOf;
use PhpBench\Assertion\Ast\ZeroValue;
use PhpBench\Assertion\Exception\ExpressionEvaluatorError;
use PhpBench\Math\FloatNumber;
use PhpBench\Math\Statistics;
use PhpBench\Util\MemoryUnit;
use PhpBench\Util\TimeUnit;
use RuntimeException;

class ExpressionEvaluator
{
    /**
     * @var array<string,mixed>
     */
    private $args;

    /**
     * @param array<string,mixed> $args
     */
    public function __construct(array $args = [])
    {
        $this->args = $args;
    }

    public function evaluate(Node $node)
    {
        if ($node instanceof IntegerNode) {
            return $node->value();
        }

        if ($node instanceof FloatNode) {
            return $node->value();
        }

        if ($node instanceof Comparison) {
            return $this->evaluateComparison($node);
        }

        if ($node instanceof TimeValue) {
            return $this->evaluateTimeValue($node);
        }

        if ($node instanceof ThroughputValue) {
            return $this->evaluateThroughputValue($node);
        }

        if ($node instanceof PropertyAccess) {
            return $this->evaluatePropertyAccess($node);
        }

        if ($node instanceof WithinRangeOf) {
            return $this->evaluateWithinRangeOf($node);
        }

        if ($node instanceof MemoryValue) {
            return $this->evaluateMemoryValue($node);
        }

        if ($node instanceof ZeroValue) {
            return $this->evaluateZeroValue($node);
        }

        throw new ExpressionEvaluatorError(sprintf(
            'Do not know how to evaluate node "%s"',
            get_class($node)
        ));
    }

    private function evaluateComparison(Comparison $node): ComparisonResult
    {
        $value1 = $node->value1();
        $value2 = $node->value2();

        $left = $this->evaluate($value1);
        $right = $this->evaluate($value2);

        // throughput is inverted
        if ($value1 instanceof ThroughputValue || $value2 instanceof ThroughputValue) {
            $left1 = $left;
            $left = $right;
            $right = $left1;
            unset($left1);
        }

        $tolerance = $this->evaluateTolerance($node->tolerance(), $right);

        if ($tolerance > 0 && FloatNumber::isWithin($left, $right - $tolerance, $right + $tolerance)) {
            return ComparisonResult::tolerated($this->formatter->format($node));
        }

        switch ($node->operator()) {
            case '<':
                if ($left < $right) {
                    return ComparisonResult::true();
                }

                return ComparisonResult::false();
            case '<=':
                if ($left <= $right) {
                    return ComparisonResult::true();
                }


                return ComparisonResult::false();
            case '=':
                if (FloatNumber::isEqual($left, $right)) {
                    return ComparisonResult::true();
                }

                return ComparisonResult::false();
            case '>':
                if ($left > $right) {
                    return ComparisonResult::true();
                }

                return ComparisonResult::false();
            case '>=':
                if (FloatNumber::isGreaterThanOrEqual($left, $right)) {
                    return ComparisonResult::true();
                }

                return ComparisonResult::false();
        }

        throw new ExpressionEvaluatorError(sprintf(
            'Do not know how to compare operator "%s"',
            $node->operator()
        ));
    }

    /**
     */
    private function evaluateTimeValue(TimeValue $node)
    {
        return TimeUnit::convert($node->value(), $node->unit(), TimeUnit::MICROSECONDS, TimeUnit::MODE_TIME);
    }

    /**
     */
    private function evaluatePropertyAccess(PropertyAccess $node)
    {
        return PropertyAccess::resolvePropertyAccess($node->segments(), $this->args);
    }

    /**
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

    private function evaluateMemoryValue(MemoryValue $node): float
    {
        return MemoryUnit::convertTo($node->value()->value(), $node->unit(), MemoryUnit::BYTES);
    }

    private function evaluateTolerance(Value $value, $right): float
    {
        if ($value instanceof PercentageValue) {
            return ($right / 100) * $value->percentage();
        }

        return $this->evaluate($value);
    }

    private function evaluateThroughputValue(ThroughputValue $node): float
    {
        return TimeUnit::convertTo(1, $node->unit(), TimeUnit::MICROSECONDS) / $node->value();
    }

    private function evaluateZeroValue(ZeroValue $node): int
    {
        return 0;
    }
}
