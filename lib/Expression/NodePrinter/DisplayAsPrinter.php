<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\DisplayAsNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Ast\UnitNode;
use PhpBench\Expression\Exception\EvaluationError;
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
    private const DEFAULT_MEMORY_UNIT = 'memory';

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

        $unitNode = $node->as();
        $value = $node->node();

        $unit = $unitNode->unit();

        if (!$unit instanceof StringNode) {
            throw new EvaluationError($node, 'Unit must evaluate to string');
        }
        $unit = $unit->value();


        if (!$value instanceof PhpValue) {
            return sprintf('%s as %s', $printer->print($value, $params), $unit);
        }

        if ($unit === self::DEFAULT_TIME_UNIT) {
            $paramUnit = isset($params[self::PARAM_OUTPUT_TIME_UNIT]) ? $params[self::PARAM_OUTPUT_TIME_UNIT] : null;

            return $this->timeUnit(
                $value->value(),
                $paramUnit,
                isset($params[self::PARAM_OUTPUT_TIME_PRECISION]) ? $params[self::PARAM_OUTPUT_TIME_PRECISION] : null,
                $printer->print(
                    new UnitNode(new StringNode($this->timeUnit->getDestSuffix($paramUnit))),
                    $params
                )
            );
        }

        if ($unit === self::DEFAULT_MEMORY_UNIT) {
            return $this->memoryUnit(
                $value->value(),
                MemoryUnit::BYTES
            );
        }

        if (TimeUnit::isTimeUnit($unit)) {
            return $this->timeUnit(
                $value->value(),
                $unit,
                null,
                $printer->print(
                    new UnitNode(new StringNode($this->timeUnit->getDestSuffix($unit))),
                    $params
                )
            );
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
            [
                self::DEFAULT_TIME_UNIT,
                self::DEFAULT_MEMORY_UNIT,
            ],
            TimeUnit::supportedUnitNames(),
            MemoryUnit::supportedUnitNames()
        );
    }

    private function timeUnit(float $value, ?string $unit, ?int $precision, string $prettyUnit): string
    {
        return sprintf('%s%s', number_format(
            $this->timeUnit->toDestUnit($value, $unit),
            $precision ?: $this->timeUnit->getPrecision()
        ), $prettyUnit);
    }

    /**
     * Return memory in bytes
     */
    private function memoryUnit(float $value, string $unit): string
    {
        return sprintf('%s %s', MemoryUnit::convertTo($value, MemoryUnit::BYTES, $unit), $unit);
    }
}
