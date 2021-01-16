<?php

namespace PhpBench\Assertion\Printer;

use PhpBench\Assertion\Ast\Comparison;
use PhpBench\Assertion\Ast\FloatNode;
use PhpBench\Assertion\Ast\IntegerNode;
use PhpBench\Assertion\Ast\MemoryValue;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Ast\PercentageValue;
use PhpBench\Assertion\Ast\PropertyAccess;
use PhpBench\Assertion\Ast\ThroughputValue;
use PhpBench\Assertion\Ast\TimeValue;
use PhpBench\Assertion\Ast\ToleranceNode;
use PhpBench\Assertion\Ast\Value;
use PhpBench\Assertion\Ast\ZeroValue;
use PhpBench\Assertion\ExpressionEvaluator;
use PhpBench\Assertion\MessageFormatter;
use PhpBench\Util\MemoryUnit;
use PhpBench\Util\TimeUnit;

final class NodePrinter implements MessageFormatter
{
    const DECIMAL_PRECISION = 3;

    /**
     * @var array
     */
    private $args;

    /**
     * @var TimeUnit
     */
    private $timeUnit;

    /**
     * @var ExpressionEvaluator
     */
    private $evaulator;

    /**
     * @param array<string,mixed> $args
     */
    public function __construct(array $args, TimeUnit $timeUnit, ExpressionEvaluator $evaulator)
    {
        $this->args = $args;
        $this->timeUnit = $timeUnit;
        $this->evaulator = $evaulator;
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

        if ($node instanceof ThroughputValue) {
            return $this->formatThroughputValue($node);
        }

        if ($node instanceof MemoryValue) {
            return $this->formatMemoryValue($node);
        }

        if ($node instanceof ZeroValue) {
            return $this->formatZeroValue($node);
        }

        if ($node instanceof IntegerNode) {
            return (string)$node->value();
        }

        if ($node instanceof FloatNode) {
            return (string)number_format($node->value(), self::DECIMAL_PRECISION);
        }

        return sprintf('!!!! could not format "%s" !!!!', get_class($node));
    }

    private function formatComparison(Comparison $node): string
    {
        return (function (?ToleranceNode $tolerance, Value $value1, string $operator, Value $value2) {
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
        return $this->timeUnit->format($this->evaulator->evaluate($timeValue), $timeValue->unit());
    }

    private function formatPropertyAccess(PropertyAccess $value): string
    {
        return (string)PropertyAccess::resolvePropertyAccess($value->segments(), $this->args);
    }

    private function formatPercentageValue(PercentageValue $node): string
    {
        return sprintf('%s%%', $node->percentage());
    }

    private function formatThroughputValue(ThroughputValue $node): string
    {
        if (array_key_exists($node->unit(), $this->aliases)) {
            $unit = $this->aliases[$node->unit()];
        } else {
            $unit = $node->unit();
        }

        return sprintf('%s ops/%s', $node->value(), $unit);
    }

    private function formatMemoryValue(MemoryValue $node): string
    {
        return sprintf(
            '%s %s',
            $this->format($node->value()),
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

    private function formatZeroValue(ZeroValue $node): string
    {
        return '0';
    }
}
