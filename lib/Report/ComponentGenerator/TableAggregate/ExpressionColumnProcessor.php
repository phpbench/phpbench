<?php

namespace PhpBench\Report\ComponentGenerator\TableAggregate;

use PhpBench\Data\DataFrame;
use PhpBench\Report\Bridge\ExpressionBridge;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExpressionColumnProcessor implements ColumnProcessorInterface
{
    /**
     * @var ExpressionBridge
     */
    private $evaluator;

    public function __construct(ExpressionBridge $evaluator)
    {
        $this->evaluator = $evaluator;
    }

    /**
     * @param tableRow $row
     * @param tableColumnDefinition $definition
     * @param parameters $params
     *
     * @return tableRow $row
     */
    public function process(array $row, array $definition, DataFrame $frame, array $params): array
    {
        $row[(string)$definition['name']] = $this->evaluator->evaluate($definition['expression'], $params);

        return $row;
    }

    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $options): void
    {
        $options->setRequired(['name', 'expression']);
        $options->setAllowedTypes('name', 'string');
        $options->setAllowedTypes('expression', 'string');
    }
}
