<?php

namespace PhpBench\Report\ComponentGenerator\TableAggregate;

use PhpBench\Data\DataFrame;
use PhpBench\Report\Bridge\ExpressionBridge;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExpandColumnProcessor implements ColumnProcessorInterface
{
    public const PARAM_VAR = 'var';
    public const PARAM_COLS = 'cols';
    public const PARAM_PARTITION = 'partition';
    public const PARAM_KEY_VAR = 'key_var';


    /**
     * @var ExpressionBridge
     */
    private $evaluator;

    public function __construct(ExpressionBridge $evaluator)
    {
        $this->evaluator = $evaluator;
    }

    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $options): void
    {
        $options->setRequired([
            self::PARAM_PARTITION,
            self::PARAM_COLS,
        ]);
        $options->setDefault(self::PARAM_VAR, self::PARAM_PARTITION);
        $options->setDefault(self::PARAM_KEY_VAR, 'key');
        $options->setAllowedTypes(self::PARAM_PARTITION, ['string', 'array']);
        $options->setAllowedTypes(self::PARAM_COLS, 'array');
        $options->setAllowedTypes(self::PARAM_VAR, 'string');
    }

    /**
     * {@inheritDoc}
     */
    public function process(array $row, array $definition, DataFrame $frame, array $params): array
    {
        if (empty($definition[self::PARAM_PARTITION])) {
            return $row;
        }

        $iterable = $this->evaluator->partition($frame, (array)$definition[self::PARAM_PARTITION]);

        foreach ($iterable as $itemKey => $itemPartition) {
            $iterationParams = array_merge($params, [
                (string)$definition[self::PARAM_KEY_VAR] => $itemKey,
                (string)$definition[self::PARAM_VAR] => $itemPartition,
            ]);

            foreach ($definition[self::PARAM_COLS] as $template => $expression) {
                $colName = $this->evaluator->renderTemplate($template, $iterationParams);

                if (isset($row[$colName])) {
                    throw new \RuntimeException(sprintf(
                        'Column name "%s" has already been set. All column keys must currently be unique (regardless of grouping)',
                        $colName
                    ));
                }
                $row[$colName] = $this->evaluator->evaluate($expression, $iterationParams);
            }
        }

        return $row;
    }
}
