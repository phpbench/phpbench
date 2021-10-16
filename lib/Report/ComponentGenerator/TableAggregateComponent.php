<?php

namespace PhpBench\Report\ComponentGenerator;

use PhpBench\Compat\SymfonyOptionsResolverCompat;
use PhpBench\Data\DataFrame;
use PhpBench\Report\Bridge\ExpressionBridge;
use PhpBench\Report\ComponentGeneratorInterface;
use PhpBench\Report\ComponentGenerator\TableAggregate\ColumnProcessorInterface;
use PhpBench\Report\ComponentInterface;
use PhpBench\Report\Model\Builder\TableBuilder;
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TableAggregateComponent implements ComponentGeneratorInterface
{
    public const PARAM_TITLE = 'title';
    public const PARAM_PARTITION = 'partition';
    public const PARAM_ROW = 'row';

    /**
     * @var ExpressionBridge
     */
    private $evaluator;

    /**
     * @var array<string, ColumnProcessorInterface>
     */
    private $columnProcessors;

    /**
     * @param array<string, ColumnProcessorInterface> $columnProcessors
     */
    public function __construct(ExpressionBridge $evaluator, array $columnProcessors = [])
    {
        $this->evaluator = $evaluator;
        $this->columnProcessors = $columnProcessors;
    }

    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $options): void
    {
        $options->setDefaults([
            self::PARAM_TITLE => null,
            self::PARAM_PARTITION => [],
            self::PARAM_ROW => [
            ],
        ]);
        $options->setAllowedTypes(self::PARAM_TITLE, ['string', 'null']);
        $options->setAllowedTypes(self::PARAM_PARTITION, ['string', 'string[]']);
        $options->setAllowedTypes(self::PARAM_ROW, 'array');
        SymfonyOptionsResolverCompat::setInfos($options, [
            self::PARAM_TITLE => 'Caption for the table',
            self::PARAM_PARTITION => 'Partition the data using these column names - the row expressions will to aggregate the data in each partition',
            self::PARAM_ROW => 'Set of expressions used to evaluate the partitions, the key is the column name, the value is the expression',
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function generateComponent(DataFrame $dataFrame, array $config): ComponentInterface
    {
        $rows = [];

        foreach ($this->evaluator->partition($dataFrame, (array)$config[self::PARAM_PARTITION]) as $dataFrameRow) {
            $row = [];

            foreach ($config[self::PARAM_ROW] as $colName => $expression) {
                if (is_string($expression)) {
                    $expression = [
                        'type' => 'expression',
                        'expression' => $expression,
                        'name' => $colName,
                    ];
                }

                if (is_array($expression)) {
                    $row = $this->processColumnDefinition($row, $expression, $dataFrameRow, $dataFrame);

                    continue;
                }

                throw new RuntimeException(sprintf(
                    'Got "%s" as type for expression, but must either be a string or a column defintion',
                    gettype($expression)
                ));
            }
            $rows[] = $row;
        }

        $builder = TableBuilder::create()
            ->addRowsFromArray($rows);

        if ($config[self::PARAM_TITLE]) {
            $builder = $builder->withTitle(
                $this->evaluator->renderTemplate($config[self::PARAM_TITLE], [
                    'frame' => $dataFrame
                ])
            );
        }

        return $builder->build();
    }

    /**
     * @param array<string,mixed> $row
     * @param array<string,mixed> $definition
     *
     * @return parameters
     */
    private function processColumnDefinition(array $row, array $definition, DataFrame $partition, DataFrame $frame): array
    {
        if (!isset($definition['type'])) {
            throw new RuntimeException(sprintf(
                'Column definition must define a "type" key, known types: "%s"',
                implode('", "', array_keys($this->columnProcessors))
            ));
        }

        $type = $definition['type'];

        if (!isset($this->columnProcessors[$type])) {
            throw new RuntimeException(sprintf(
                'Unknown column processor "%s", known column proccessors: "%s"',
                $type,
                implode('", "', array_keys($this->columnProcessors))
            ));
        }

        unset($definition['type']);

        $processor = $this->columnProcessors[$type];
        $resolver = new OptionsResolver();
        $processor->configure($resolver);

        return $processor->process($row, $resolver->resolve($definition), $partition, [
            'frame' => $frame,
            'partition' => $partition,
        ]);
    }
}
