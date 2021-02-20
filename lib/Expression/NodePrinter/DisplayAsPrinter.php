<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Assertion\Exception\ExpressionEvaluatorError;
use PhpBench\Expression\Ast\DisplayAsNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NumberNode;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Exception\PrinterError;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;
use PhpBench\Util\MemoryUnit;
use PhpBench\Util\TimeUnit;

class DisplayAsPrinter implements NodePrinter
{
    /**
     * @param parameters $params
     */
    public function print(Printer $printer, Node $node, array $params): ?string
    {
        if (!$node instanceof DisplayAsNode) {
            return null;
        }

        $unit = $node->as();
        $value = $node->node();

        if (!$value instanceof PhpValue) {
            return sprintf('%s as %s', $printer->print($value, $params), $unit);
        }

        if (TimeUnit::isTimeUnit($unit)) {
            return $this->timeUnit($value->value(), $unit);
        }

        if (MemoryUnit::isMemoryUnit($unit)) {
            return $this->memoryUnit($value->value(), $unit);
        }

        throw new ExpressionEvaluatorError(sprintf(
            'Do not know how to display unit "%s"', $unit
        ));
    }

    /**
     * Return time in microseconds
     */
    private function timeUnit(float $value, string $unit): string
    {
        return sprintf(
            '%s %s',
            TimeUnit::convert($value, TimeUnit::MICROSECONDS, $unit, TimeUnit::MODE_TIME),
            TimeUnit::getSuffix($unit)
        );
    }

    /**
     * Return memory in bytes
     */
    private function memoryUnit(float $value, string $unit): string
    {
        return sprintf('%s %s', MemoryUnit::convertTo($value, MemoryUnit::BYTES, $unit), $unit);
    }
}
