<?php

namespace PhpBench\Report\ComponentGenerator;

use PhpBench\Compat\SymfonyOptionsResolverCompat;
use PhpBench\Data\DataFrame;
use PhpBench\Report\Bridge\ExpressionBridge;
use PhpBench\Report\ComponentGeneratorInterface;
use PhpBench\Report\ComponentInterface;
use PhpBench\Report\Model\Builder\TableBuilder;
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

    public function __construct(ExpressionBridge $evaluator)
    {
        $this->evaluator = $evaluator;
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
                $row[$colName] = $this->evaluator->evaluate($expression, [
                    'partition' => $dataFrameRow,
                    'frame' => $dataFrame,
                ]);
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
}
