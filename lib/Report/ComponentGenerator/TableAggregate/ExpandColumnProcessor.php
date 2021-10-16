<?php

namespace PhpBench\Report\ComponentGenerator\TableAggregate;

use PhpBench\Report\Bridge\ExpressionBridge;
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function is_iterable;

class ExpandColumnProcessor implements ColumnProcessorInterface
{
    /**
     * @var ExpressionBridge
     */
    private $evaluator;

    public function __construct(ExpressionBridge $evaluator) {
        $this->evaluator = $evaluator;
    }

    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $options): void
    {
        $options->setRequired([
            'each',
            'cols',
        ]);
        $options->setDefault('param', 'item');
        $options->setAllowedTypes('each', 'string');
        $options->setAllowedTypes('cols', 'array');
    }

    /**
     * {@inheritDoc}
     */
    public function process(array $row, array $definition, array $params): array
    {
        if (empty($definition['each'])) {
            return $row;
        }
        $iterable = $this->evaluator->evaluatePhpValue($definition['each'], $params);

        if (!is_iterable($iterable )) {
            throw new RuntimeException(sprintf(
                'Evaluated value for "expand" column must evaluate to a list got "%s"',
                gettype($iterable)
            ));
        }

        foreach ($iterable as $item) {
            $iterationParams = array_merge($params, [
                (string)$definition['param'] => $item,
            ]);
            foreach ($definition['cols'] as $template => $expression) {
                $row[$this->evaluator->renderTemplate($template, $iterationParams)] = $this->evaluator->evaluate($expression, $iterationParams);
            }
        }

        return $row;
    }
}
