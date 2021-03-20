<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\DisplayAsNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Exception\PrinterError;
use PhpBench\Expression\NodePrinter;
use PhpBench\Expression\Printer;
use PhpBench\Util\MemoryUnit;
use PhpBench\Util\TimeUnit;

class DisplayAsPrinter implements NodePrinter
{
    public const PARAM_OUTPUT_TIME_UNIT = 'output_time_unit';
    public const PARAM_OUTPUT_TIME_PRECISION = 'output_time_precision';

    private const DEFAULT_TIME_UNIT = 'time';


    /**
     * @var TimeUnit
     */
    private $timeUnit;

    public function __construct(TimeUnit $timeUnit)
    {
        $this->timeUnit = $timeUnit;
    }

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

        if ($unit === self::DEFAULT_TIME_UNIT) {
            return $this->timeUnit(
                $value->value(),
                isset($params[self::PARAM_OUTPUT_TIME_UNIT]) ? $params[self::PARAM_OUTPUT_TIME_UNIT] : null,
                isset($params[self::PARAM_OUTPUT_TIME_PRECISION]) ? $params[self::PARAM_OUTPUT_TIME_PRECISION] : null
            );
        }

        if (TimeUnit::isTimeUnit($unit)) {
            return $this->timeUnit($value->value(), $unit, null);
        }

        if (MemoryUnit::isMemoryUnit($unit)) {
            return $this->memoryUnit($value->value(), $unit);
        }

        throw new PrinterError(sprintf(
            'Do not know how to display unit "%s"', $unit
        ));
    }

    /**
     * @return string[]
     */
    public static function supportedUnitNames(): array
    {
        return array_merge(
            [self::DEFAULT_TIME_UNIT],
            TimeUnit::supportedUnitNames(),
            MemoryUnit::supportedUnitNames()
        );
    }

    private function timeUnit(float $value, ?string $unit, ?int $precision): string
    {
        return $this->timeUnit->format($value, $unit, null, $precision);
    }

    /**
     * Return memory in bytes
     */
    private function memoryUnit(float $value, string $unit): string
    {
        return sprintf('%s %s', MemoryUnit::convertTo($value, MemoryUnit::BYTES, $unit), $unit);
    }
}
