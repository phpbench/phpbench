<?php

namespace PhpBench\Report\ComponentGenerator;

use PhpBench\Compat\SymfonyOptionsResolverCompat;
use PhpBench\Data\DataFrame;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\ExpressionEvaluator;
use PhpBench\Report\ComponentGeneratorInterface;
use PhpBench\Report\ComponentInterface;
use PhpBench\Report\Model\Table;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TableAggregateComponent implements ComponentGeneratorInterface
{
    public const PARAM_CAPTION = 'caption';
    public const PARAM_PARTITION = 'partition';
    public const PARAM_ROW = 'row';

    /**
     * @var ExpressionEvaluator
     */
    private $evaluator;

    public function __construct(ExpressionEvaluator $evaluator)
    {
        $this->evaluator = $evaluator;
    }

    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $options): void
    {
        $options->setDefaults([
            self::PARAM_CAPTION => null,
            self::PARAM_PARTITION => [],
            self::PARAM_ROW => [
            ],
        ]);
        $options->setAllowedTypes(self::PARAM_CAPTION, ['string', 'null']);
        $options->setAllowedTypes(self::PARAM_PARTITION, 'array');
        $options->setAllowedTypes(self::PARAM_ROW, 'array');
        SymfonyOptionsResolverCompat::setInfos($options, [
            self::PARAM_CAPTION => 'Caption for the table',
            self::PARAM_PARTITION => 'Partition the data using these columns - the row expressions will to aggregate the data in each partition',
            self::PARAM_ROW => 'Set of expressions used to evaluate the partitions, the key is the column name, the value is the expression',
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function generateComponent(DataFrame $dataFrame, array $config): ComponentInterface
    {
        $rows = [];
        foreach ($dataFrame->partition($config[self::PARAM_PARTITION]) as $dataFrameRow) {
            $row = [];
            foreach ($config[self::PARAM_ROW] as $colName => $expression) {
                $row[$colName] = $this->evaluator->evaluate($expression, [
                    'partition' => $dataFrameRow,
                    'frame' => $dataFrame,
                ]);
            }
            $rows[] = $row;
        }

        return Table::fromRowArray($rows, $config[self::PARAM_CAPTION]);
    }
}
