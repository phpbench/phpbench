<?php

namespace PhpBench\Expression\NodePrinter;

use PhpBench\Expression\Ast\DisplayAsNode;
use PhpBench\Expression\Ast\DisplayAsTimeNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NullNode;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Ast\UnitNode;
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

        $value = $node->node();
        $unitNode = $node->as();

        $unit = $unitNode->unit();

        if (!$unit instanceof StringNode) {
            throw new PrinterError(sprintf('Unit must evaluate to string, got "%s"', get_class($unit)));
        }
        $unit = $unit->value();
        $mode = $this->resolveMode($node);

        if (!$value instanceof PhpValue) {
            return sprintf('%s as %s', $printer->print($value, $params), $unit);
        }

        if (TimeUnit::isTimeUnit($unit)) {
            return $this->timeUnit(
                $value->value(),
                $unit,
                $this->resolvePrecision($node->precision()),
                $printer->print(
                    new UnitNode(new StringNode($this->timeUnit->getDestSuffix(
                        $this->timeUnit->resolveDestUnit($unit),
                        $mode
                    ))),
                    $params
                ),
                $mode
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
            TimeUnit::supportedUnitNames(),
            MemoryUnit::supportedUnitNames()
        );
    }

    private function timeUnit(float $value, ?string $unit, ?int $precision, string $prettyUnit, ?string $mode): string
    {
        return sprintf('%s%s', number_format(
            $this->timeUnit->toDestUnit(
                $value,
                $this->timeUnit->resolveDestUnit($unit),
                $mode
            ),
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

    private function resolvePrecision(?Node $node): ?int
    {
        if ($node instanceof IntegerNode) {
            return $node->value();
        }

        return null;
    }

    private function resolveMode(DisplayAsNode $node): ?string
    {
        if (!$node instanceof DisplayAsTimeNode) {
            return null;
        }
        
        $mode = $node->mode();

        if (!$mode) {
            return null;
        }

        if ($mode instanceof NullNode) {
            return null;
        }

        if (!$mode instanceof StringNode) {
            throw new PrinterError(sprintf('Time mode must evaluate to string, got "%s"', get_class($mode)));
        }
        
        return $mode->value();
    }
}
