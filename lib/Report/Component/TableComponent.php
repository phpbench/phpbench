<?php

namespace PhpBench\Report\Component;

use PhpBench\Data\DataFrame;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\ExpressionEvaluator;
use PhpBench\Report\ComponentGeneratorInterface;
use PhpBench\Report\ComponentInterface;
use PhpBench\Report\Model\Table;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TableComponent implements ComponentGeneratorInterface
{
    public const PARAM_TITLE = 'title';
    public const PARAM_PARTITION = 'partition';
    public const PARAM_COLUMN_EXPRESSIONS = 'column_expressions';

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
            self::PARAM_TITLE => null,
            self::PARAM_PARTITION => [],
            self::PARAM_COLUMN_EXPRESSIONS => [
            ],
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
            foreach ($config[self::PARAM_COLUMN_EXPRESSIONS] as $colName => $expression) {
                $row[$colName] = $this->evaluator->evaluate($expression, [
                    'partition' => $dataFrameRow,
                    'frame' => $dataFrame,
                ]);
            }
            $rows[] = $row;
        }

        $title = null;
        if ($config[self::PARAM_TITLE]) {
            $title = $this->evaluator->evaluate($config[self::PARAM_TITLE], [
                'frame' => $dataFrame
            ]);
            assert($title instanceof PhpValue);
            $title = $title->value();
        }

        return Table::fromRowArray($rows, $title);
    }
}
