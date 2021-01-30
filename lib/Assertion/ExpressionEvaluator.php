<?php

namespace PhpBench\Assertion;

use PhpBench\Assertion\Ast\Comparison;
use PhpBench\Assertion\Ast\DisplayAsNode;
use PhpBench\Assertion\Ast\ExpressionNode;
use PhpBench\Assertion\Ast\FloatNode;
use PhpBench\Assertion\Ast\FunctionNode;
use PhpBench\Assertion\Ast\IntegerNode;
use PhpBench\Assertion\Ast\MemoryValue;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Ast\PercentageValue;
use PhpBench\Assertion\Ast\PropertyAccess;
use PhpBench\Assertion\Ast\ThroughputValue;
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

        if ($node instanceof DisplayAsNode) {
            return $this->evaluateDisplayasNode($node);
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
            return ($right / 100) * $value->percentage()->value();
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
}
