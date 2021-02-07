<?php

namespace PhpBench\Assertion\Printer;

use PhpBench\Assertion\ArithmeticNode;
use PhpBench\Assertion\Ast\Comparison;
use PhpBench\Assertion\Ast\DisplayAsNode;
use PhpBench\Assertion\Ast\ExpressionNode;
use PhpBench\Assertion\Ast\FloatNode;
use PhpBench\Assertion\Ast\FunctionNode;
use PhpBench\Assertion\Ast\IntegerNode;
use PhpBench\Assertion\Ast\MemoryUnitNode;
use PhpBench\Assertion\Ast\MemoryValue;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Ast\ParenthesizedExpressionNode;
use PhpBench\Assertion\Ast\PercentageValue;
use PhpBench\Assertion\Ast\PropertyAccess;
use PhpBench\Assertion\Ast\ThroughputValue;
use PhpBench\Assertion\Ast\TimeUnitNode;
use PhpBench\Assertion\Ast\TimeValue;
use PhpBench\Assertion\Ast\ToleranceNode;
use PhpBench\Assertion\ExpressionEvaluator;
use PhpBench\Assertion\ExpressionEvaluatorFactory;
use PhpBench\Assertion\ExpressionPrinter;
use PhpBench\Util\MemoryUnit;
use PhpBench\Util\TimeUnit;

final class NodePrinter implements ExpressionPrinter
{
    const DECIMAL_PRECISION = 3;

    /**
     * @var parameters
     */
    private $parameters;

    /**
     * @var TimeUnit
     */
    private $timeUnit;

    /**
     * @var ExpressionEvaluator
     */
    private $evaulator;

    /**
     * @param parameters $parameters
     */
    public function __construct(
        array $parameters,
        TimeUnit $timeUnit,
        ExpressionEvaluatorFactory $evaulator
    ) {
        $this->parameters = $parameters;
        $this->timeUnit = $timeUnit;
        $this->evaulator = $evaulator->createWithParameters($parameters);
    }

    public function format(Node $node): string
    {
        if ($node instanceof Comparison) {
            return $this->formatComparison($node);
        }

        if ($node instanceof TimeValue) {
            return $this->formatTimeValue($node);
        }

        if ($node instanceof PropertyAccess) {
            return $this->formatPropertyAccess($node);
        }

        if ($node instanceof PercentageValue) {
            return $this->formatPercentageValue($node);
        }

        if ($node instanceof MemoryValue) {
            return $this->formatMemoryValue($node);
        }

        if ($node instanceof IntegerNode) {
            return (string)$node->value();
        }

        if ($node instanceof FunctionNode) {
            return $this->formatFunctionNode($node);
        }

        if ($node instanceof DisplayAsNode) {
            return $this->formatDisplayAsNode($node);
        }

        if ($node instanceof FloatNode) {
            return (string)number_format($node->value(), self::DECIMAL_PRECISION);
        }

        if ($node instanceof ParenthesizedExpressionNode) {
            return $this->format($node->expression());
        }

        if ($node instanceof ArithmeticNode) {
            return $this->evaulator->evaluate($node);
        }

        if ($node instanceof ThroughputValue) {
            return $this->formatThroughput($node);
        }

        return sprintf('!!!! could not format "%s" !!!!', get_class($node));
    }

    private function formatComparison(Comparison $node): string
    {
        return (function (?ToleranceNode $tolerance, ExpressionNode $value1, string $operator, ExpressionNode $value2) {
            switch ($operator) {
                case '=':
                    $operator = '≈';

                    break;
            }

            if ($tolerance) {
                return sprintf(
                    '%s %s %s ± %s',
                    $this->format($value1),
                    $operator,
                    $this->format($value2),
                    $this->format($tolerance->tolerance())
                );
            }

            return sprintf(
                '%s %s %s',
                $this->format($value1),
                $operator,
                $this->format($value2)
            );
        })($node->tolerance(), $node->value1(), $node->operator(), $node->value2());
    }

    private function formatTimeValue(TimeValue $timeValue): string
    {
        return $this->timeUnit->format(
            $this->evaulator->evaluate($timeValue),
            $timeValue->unit(),
            null,
            $timeValue->unit() === TimeUnit::MICROSECONDS ? 0 : null
        );
    }

    private function formatPropertyAccess(PropertyAccess $value): string
    {
        return (string)PropertyAccess::resolvePropertyAccess($value->segments(), $this->parameters);
    }

    private function formatPercentageValue(PercentageValue $node): string
    {
        return sprintf('%s%%', $this->format($node->percentage()));
    }

    private function formatMemoryValue(MemoryValue $node): string
    {
        return sprintf(
            '%s %s',
            number_format(MemoryUnit::convertTo(
                $this->evaulator->evaluate($node),
                MemoryUnit::BYTES,
                $node->unit()
            )),
            $node->unit()
        );
    }

    private function formatNumberValue(float $value): string
    {
        // if value has a fractional part, limit the precision
        if (false !== strpos((string)$value, '.')) {
            return number_format($value, self::DECIMAL_PRECISION);
        }

        // else only format the integer value
        return number_format($value);
    }

    private function formatFunctionNode(FunctionNode $node): string
    {
        $value = $this->evaulator->evaluate($node);

        return (string)$value;
    }

    private function formatDisplayAsNode(DisplayAsNode $node): string
    {
        if ($node->unit() instanceof TimeUnitNode) {
            return $this->timeUnit->format(
                $this->evaulator->evaluate($node->node()),
                $node->unit()->unit()
            );
        }

        if ($node->unit() instanceof MemoryUnitNode) {
            return (string)MemoryUnit::convertTo(
                $this->evaulator->evaluate($node->node()),
                MemoryUnit::BYTES,
                $node->unit()->unit()
            );
        }

        return '';
    }

    private function formatThroughput(ThroughputValue $node): string
    {
        return sprintf('%s ops/%s', $this->evaulator->evaluate($node), $node->unit()->unit());
    }
}
