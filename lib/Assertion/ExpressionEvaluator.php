<?php

namespace PhpBench\Assertion;

use PhpBench\Assertion\Ast\Assertion;
use PhpBench\Assertion\Ast\Comparison;
use PhpBench\Assertion\Ast\MemoryValue;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Ast\PercentageValue;
use PhpBench\Assertion\Ast\PropertyAccess;
use PhpBench\Assertion\Ast\TimeValue;
use PhpBench\Assertion\Ast\WithinRangeOf;
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
     * @var MessageFormatter
     */
    private $formatter;

    /**
     * @param array<string,mixed> $args
     */
    public function __construct(MessageFormatter $formatter, array $args = [])
    {
        $this->args = $args;
        $this->formatter = $formatter;
    }

    public function assert(Assertion $node): AssertionResult
    {
        $result = $this->evaluate($node);

        if (!$result instanceof AssertionResult) {
            throw new RuntimeException(sprintf(
                'Assertion node "%s" did not evaluate to an AssertionResult, evaluated to "%s"',
                get_class($node),
                is_object($result) ? get_class($result) : gettype($result)
            ));
        }

        return $result;
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

        if ($node instanceof MemoryValue) {
            return $this->evaluateMemoryValue($node);
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
        $tolerance = $this->evaluate($node->tolerance());

        switch ($node->operator()) {
            case '<':
                if ($left >= $right) {
                    if ($left < ($right + $tolerance)) {
                        return AssertionResult::tolerated($this->formatter->format($node));
                    }

                    return AssertionResult::fail($this->formatter->format($node));
                }

                return AssertionResult::ok();
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

    private function evaluateMemoryValue(MemoryValue $node): int
    {
        return MemoryUnit::convertToBytes($node->value(), $node->unit());
    }
}
