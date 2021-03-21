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
            throw new PrinterError(sprintf('Unit must evaluate to string, got "%s"', get_class($unitNode)));
        }

        if (!$value instanceof PhpValue) {
            return sprintf('%s as %s', $printer->print($value, $params), $unitNode->value());
        }

        $unitValue = $unitNode->value();

        return (function (UnitValue $value) use ($printer, $unitValue, $params) {
            return sprintf(
                '%s%s',
                number_format($value->value(), floor($value->value()) === $value->value() ? 0 : 3),
                $printer->print(new UnitNode(new StringNode(UnitConverter::suffix($unitValue))), $params)
            );
        })(UnitConverter::convert(UnitConverter::MICROSECOND, $unitValue, $value->value()));
    }

    /**
     * @return string[]
     */
    public static function supportedUnitNames(): array
    {
        return array_merge(
            UnitConverter::supportedUnits()
        );
    }
}
