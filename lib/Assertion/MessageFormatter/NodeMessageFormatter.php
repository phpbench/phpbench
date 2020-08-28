<?php

namespace PhpBench\Assertion\MessageFormatter;

use PhpBench\Assertion\Ast\Comparison;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Ast\PropertyAccess;
use PhpBench\Assertion\Ast\TimeValue;
use PhpBench\Assertion\Ast\Value;
use PhpBench\Assertion\MessageFormatter;
use PhpBench\Util\TimeUnit;

final class NodeMessageFormatter implements MessageFormatter
{
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


        return sprintf('<could not format "%s"', get_class($node));
    }

    private function formatComparison(Comparison $node): string
    {
        $value1 = $node->value1();
        $value2 = $node->value2();

        if ($value1 instanceof PropertyAccess && $value2 instanceof TimeValue) {
            $value1Formatted = $this->formatTimeValue(
                new TimeValue(
                    TimeUnit::convertTo(
                        $this->formatPropertyAccess($value1),
                        TimeUnit::MICROSECONDS,
                        $value2->unit()
                    ),
                    $value2->unit()
                )
            );
        } else {
            $value1Formatted = $this->format($value1);
        }
        $message = sprintf(
            '%s %s %s ± %s',
            $value1Formatted,
            $node->operator(),
            $this->format($value2),
            $this->format($node->tolerance())
        );

        return $message;
    }

    private function formatTimeValue(TimeValue $timeValue): string
    {
        $value = $timeValue->value();
        if (false !== strpos((string)$value, '.')) {
            $value = number_Format($timeValue->value(), 3);
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
}
