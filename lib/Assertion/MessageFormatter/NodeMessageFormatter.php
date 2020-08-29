<?php

namespace PhpBench\Assertion\MessageFormatter;

use PhpBench\Assertion\Ast\Comparison;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Ast\PercentageValue;
use PhpBench\Assertion\Ast\PropertyAccess;
use PhpBench\Assertion\Ast\TimeValue;
use PhpBench\Assertion\Ast\Value;
use PhpBench\Assertion\MessageFormatter;
use PhpBench\Util\TimeUnit;

final class NodeMessageFormatter implements MessageFormatter
{
    const DECIMAL_PRECISION = 3;

    /**
     * @var array
     */
    private $args;

    /**
     * @var array<string,string>
     */
    private $aliases = [
        TimeUnit::MICROSECONDS => 'μs',
        TimeUnit::MILLISECONDS => 'ms',
        TimeUnit::SECONDS => 's',
    ];

    public function __construct(array $args)
    {
        $this->args = $args;
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

        return sprintf('!!!! could not format "%s" !!!!', get_class($node));
    }

    private function formatComparison(Comparison $node): string
    {
        $value1 = $node->value1();
        $value2 = $node->value2();

        $message = sprintf(
            '%s %s %s ± %s',
            $this->formatValueWithNormalizedUnit($value1, $value2, $node->tolerance()),
            $node->operator(),
            $this->formatValueWithNormalizedUnit($value2, $value1, $node->tolerance()),
            $this->formatValueWithNormalizedUnit($node->tolerance(), $value1, $value2)
        );

        return $message;
    }

    private function formatTimeValue(TimeValue $timeValue): string
    {
        $value = $timeValue->value();

        if (false !== strpos((string)$value, '.')) {
            // if value has a fractional part, limit the precision
            $value = number_format($timeValue->value(), self::DECIMAL_PRECISION);
        } else {
            // else only format the integer value
            $value = number_format($timeValue->value());
        }

        if (array_key_exists($timeValue->unit(), $this->aliases)) {
            return sprintf('%s%s', $value, $this->aliases[$timeValue->unit()]);
        }

        return sprintf('%s %s', $value, $timeValue->unit());
    }

    private function formatPropertyAccess(PropertyAccess $value): string
    {
        return (string)PropertyAccess::resolvePropertyAccess($value->segments(), $this->args);
    }

    private function formatValueWithNormalizedUnit(Value $value, Value ...$companionValues): string
    {
        if (!$value instanceof PropertyAccess) {
            return $this->format($value);
        }

        foreach ($companionValues as $companionValue) {
            if ($companionValue instanceof TimeValue) {
                return $this->format(
                    new TimeValue(
                        TimeUnit::convertTo(
                            (float)$this->formatPropertyAccess($value),
                            TimeUnit::MICROSECONDS,
                            $companionValue->unit()
                        ),
                        $companionValue->unit()
                    )
                );
            }
        }

        return $this->format($value);
    }

    private function formatPercentageValue(PercentageValue $node): string
    {
        return sprintf('%s%%', $node->percentage());
    }
}
