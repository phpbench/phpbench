<?php

namespace PhpBench\Assertion\MessageFormatter;

use PhpBench\Assertion\Ast\Comparison;
use PhpBench\Assertion\Ast\Node;
use PhpBench\Assertion\Ast\PropertyAccess;
use PhpBench\Assertion\Ast\TimeValue;
use PhpBench\Assertion\Ast\Value;
use PhpBench\Assertion\MessageFormatter;

final class NodeMessageFormatter implements MessageFormatter
{
    /**
     * @var array
     */
    private $args;

    public function __construct(array $args)
    {
        $this->args = $args;
    }

    public function format(Node $node): string
    {
        if ($node instanceof Comparison) {
            return $this->formatComparison($node);
        }

        return sprintf('<could not format "%s"', get_class($node));
    }

    private function formatComparison(Comparison $node): string
    {
        $message = sprintf(
            '%s %s %s ± %s',
            $this->formatValue($node->value1()),
            $node->operator(),
            $this->formatValue($node->value2()),
            $this->formatValue($node->tolerance())
        );

        return $message;
    }

    private function formatValue(Value $value): string
    {
        if ($value instanceof TimeValue) {
            return $this->formatTimeValue($value);
        }

        if ($value instanceof PropertyAccess) {
            return $this->formatPropertyAccess($value);
        }

        return sprintf('<could not format "%s"', get_class($value));
    }

    private function formatTimeValue(TimeValue $value): string
    {
        return sprintf('%s %s', $value->value(), $value->unit());
    }

    private function formatPropertyAccess(PropertyAccess $value): string
    {
        return (string)PropertyAccess::resolvePropertyAccess($value->segments(), $this->args);
    }
}
