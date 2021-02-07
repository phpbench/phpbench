<?php

namespace PhpBench\Assertion;

use PhpBench\Assertion\Ast\Comparison;
use PhpBench\Assertion\Ast\DisplayAsNode;
use PhpBench\Assertion\Ast\ExpressionNode;
use PhpBench\Assertion\Ast\FloatNode;
use PhpBench\Assertion\Ast\FunctionNode;
use PhpBench\Assertion\Ast\IntegerNode;
use PhpBench\Assertion\Ast\ListNode;
use PhpBench\Assertion\Ast\MemoryValue;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Ast\ParenthesizedExpressionNode;
use PhpBench\Assertion\Ast\PercentageValue;
use PhpBench\Assertion\Ast\PropertyAccess;
use PhpBench\Assertion\Ast\ThroughputValue;
use PhpBench\Assertion\Ast\TimeUnitNode;
use PhpBench\Assertion\Ast\TimeValue;
use PhpBench\Assertion\Ast\ToleranceNode;
use PhpBench\Assertion\Exception\ExpressionEvaluatorError;
use PhpBench\Math\FloatNumber;
use PhpBench\Util\MemoryUnit;
use PhpBench\Util\TimeUnit;

class ExpressionEvaluator
{
    /**
     * @var parameters
     */
    private $parameters;

    /**
     * @var ExpressionFunctions
     */
    private $functions;

    /**
     * @param parameters $parameters
     */
    public function __construct(array $parameters = [], ?ExpressionFunctions $functions = null)
    {
        $this->parameters = $parameters;
        $this->functions = $functions ?: new ExpressionFunctions([]);
    }

    /**
     * @return mixed
     */
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

        if ($node instanceof FunctionNode) {
            return $this->evaluateFunction($node);
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

        if ($node instanceof MemoryValue) {
            return $this->evaluateMemoryValue($node);
        }

        if ($node instanceof TimeUnitNode) {
            return $this->evaluateTimeUnit($node);
        }

        if ($node instanceof DisplayAsNode) {
            return $this->evaluateDisplayasNode($node);
        }

        if ($node instanceof ArithmeticNode) {
            return $this->evaluateArithmatic($node);
        }

        if ($node instanceof ParenthesizedExpressionNode) {
            return $this->evaluateParenthesizedExpression($node);
        }

        if ($node instanceof ListNode) {
            return $this->evaluateListNode($node);
        }

        if ($node instanceof PercentageValue) {
            return $this->evaluate($node->percentage());
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

        $left = $this->evaluateComparable($value1);
        $right = $this->evaluateComparable($value2);

        $tolerance = $this->evaluateTolerance($node->tolerance(), $right);

        if ($tolerance > 0 && FloatNumber::isWithin($left, $right - $tolerance, $right + $tolerance)) {
            return ComparisonResult::tolerated();
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

    private function evaluateTimeValue(TimeValue $node): float
    {
        return TimeUnit::convert(
            $this->evaluate($node->value()),
            $node->unit(),
            TimeUnit::MICROSECONDS,
            TimeUnit::MODE_TIME
        );
    }

    /**
     * @return mixed
     */
    private function evaluatePropertyAccess(PropertyAccess $node)
    {
        return PropertyAccess::resolvePropertyAccess($node->segments(), $this->parameters);
    }

    private function evaluateMemoryValue(MemoryValue $node): float
    {
        return MemoryUnit::convertTo(
            $this->evaluate($node->value()),
            $node->unit(),
            MemoryUnit::BYTES
        );
    }

    /**
     * @param int|float $right
     */
    private function evaluateTolerance(?ToleranceNode $tolerance, $right): float
    {
        if (null === $tolerance) {
            return 0;
        }

        $value = $tolerance->tolerance();

        if ($value instanceof PercentageValue) {
            return ($right / 100) * $this->evaluate($value);
        }

        return $this->evaluate($value);
    }

    private function evaluateThroughputValue(ThroughputValue $node): float
    {
        return TimeUnit::convertTo(
            1,
            $node->unit()->unit(),
            TimeUnit::MICROSECONDS
        ) / $this->evaluate($node->value());
    }

    private function evaluateDisplayasNode(DisplayAsNode $node): float
    {
        return $this->evaluate($node->node());
    }

    /**
     * @return mixed
     */
    private function evaluateFunction(FunctionNode $node)
    {
        return $this->functions->execute($node->name(), array_map(function (ExpressionNode $node) {
            return $this->evaluate($node);
        }, $node->args()));
    }

    /**
     * @return number
     */
    private function evaluateArithmatic(ArithmeticNode $node)
    {
        $leftValue = $this->evaluateComparable($node->left());
        $rightValue = $this->evaluateComparable($node->right());

        switch ($node->operator()) {
            case '+':
                return $leftValue + $rightValue;
            case '*':
                return $leftValue * $rightValue;
            case '/':
                return $leftValue / $rightValue;
            case '-':
                return $leftValue - $rightValue;
        }

        throw new ExpressionEvaluatorError(sprintf(
            'Unknown operator "%s"',
            $node->operator()
        ));
    }

    private function evaluateParenthesizedExpression(ParenthesizedExpressionNode $node)
    {
        return $this->evaluate($node->expression());
    }

    /**
     * @return number
     */
    private function evaluateComparable(ExpressionNode $expression)
    {
        $result = $this->evaluate($expression);

        if ($result instanceof ComparisonResult) {
            return ($result->isTrue() || $result->isTolerated()) ? 1 : 0;
        }

        if (is_string($result) || !is_numeric($result)) {
            throw new ExpressionEvaluatorError(sprintf(
                'Cannot compare value of type "%s"',
                is_object($result) ? get_class($result) : gettype($result)
            ));
        }

        return $result;
    }

    private function evaluateTimeUnit(TimeUnitNode $node): int
    {
        return TimeUnit::convert(
            1,
            $node->unit(),
            TimeUnit::MICROSECONDS,
            TimeUnit::MODE_TIME
        );
    }

    private function evaluateListNode(ListNode $node)
    {
        return array_map(function (ExpressionNode $expression) {
            return $this->evaluate($expression);
        }, $node->expressions());
    }
}
