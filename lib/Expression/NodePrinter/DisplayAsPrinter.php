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
use PhpBench\Util\UnitConverter;
use PhpBench\Util\UnitValue;

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
        $unitNode = $unitNode->unit();
        $value = $node->node();

        if (!$unitNode instanceof StringNode) {
            throw new EvaluationError($node, 'Unit must evaluate to string');
        }

        if (!$value instanceof PhpValue) {
            return sprintf('%s as %s', $printer->print($value, $params), $unitNode->value());
        }

        $unitValue = $unitNode->value();
        if ($unitValue === self::DEFAULT_TIME_UNIT) {
            $unitValue = isset($params[self::PARAM_OUTPUT_TIME_UNIT]) ? $params[self::PARAM_OUTPUT_TIME_UNIT] : null;
        }

        if ($unitValue === self::DEFAULT_MEMORY_UNIT) {
            $unitValue = self::DEFAULT_MEMORY_UNIT;
        }


        return (function (UnitValue $value) use ($printer, $unitNode, $params) {
            return sprintf('%s %s', round($value->value(), 6), $printer->print($unitNode, $params));
        })(UnitConverter::convert(UnitConverter::MICROSECOND, $unitValue, $value->value()));
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
            UnitConverter::supportedUnits()
        );
    }
}
