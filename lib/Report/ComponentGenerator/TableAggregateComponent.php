<?php

namespace PhpBench\Report\ComponentGenerator;

use PhpBench\Compat\SymfonyOptionsResolverCompat;
use PhpBench\Data\DataFrame;
use PhpBench\Report\Bridge\ExpressionBridge;
use PhpBench\Report\ComponentGeneratorInterface;
use PhpBench\Report\ComponentGenerator\TableAggregate\ColumnProcessorInterface;
use PhpBench\Report\ComponentGenerator\TableAggregate\GroupHelper;
use PhpBench\Report\ComponentInterface;
use PhpBench\Report\Model\Builder\TableBuilder;
use PhpBench\Report\Model\TableColumnGroup;
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TableAggregateComponent implements ComponentGeneratorInterface
{
    public const PARAM_TITLE = 'title';
    public const PARAM_PARTITION = 'partition';
    public const PARAM_ROW = 'row';
    public const GROUP_DEFAULT = 'default';
    public const PARAM_GROUPS = 'groups';

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
            self::PARAM_GROUPS => [],
        ]);
        $options->setAllowedTypes(self::PARAM_TITLE, ['string', 'null']);
        $options->setAllowedTypes(self::PARAM_PARTITION, ['string', 'string[]']);
        $options->setAllowedTypes(self::PARAM_ROW, 'array');
        $options->setAllowedTypes(self::PARAM_GROUPS, 'array');
        SymfonyOptionsResolverCompat::setInfos($options, [
            self::PARAM_TITLE => 'Caption for the table',
            self::PARAM_PARTITION => 'Partition the data using these column names - the row expressions will to aggregate the data in each partition',
            self::PARAM_ROW => 'Set of expressions used to evaluate the partitions, the key is the column name, the value is the expression',
            self::PARAM_GROUPS => <<<'EOT'
Group columns together, e.g. ``{"groups":{"group_name": {"cols": ["col1", "col2"]}}}``
EOT
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function generateComponent(DataFrame $dataFrame, array $config): ComponentInterface
    {
        $columnDefinitions = $this->columnDefinitions($config[self::PARAM_ROW]);
        $resolvedColumns = [];

        $rows = [];

        foreach ($this->evaluator->partition($dataFrame, (array)$config[self::PARAM_PARTITION]) as $dataFrameRow) {
            $row = [];

            foreach ($columnDefinitions as $colName => $definition) {
                $newRow = $this->generateRow($row, $definition, $dataFrameRow, $dataFrame);

                if (!isset($resolvedColumns[$colName])) {
                    $resolvedColumns[$colName] = array_diff(array_keys($newRow), array_keys($row));
                }
                $row = $newRow;
            }
            $rows[] = $row;
        }

        $builder = TableBuilder::create()
            ->addRowsFromArray($rows)
            ->addGroups($this->resolveGroups($resolvedColumns, $config['groups']));

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
    private function generateRow(array $row, array $definition, DataFrame $partition, DataFrame $frame): array
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

        // could memoize this but it makes no practical difference
        $resolver = new OptionsResolver();
        $processor->configure($resolver);

        return $processor->process($row, $resolver->resolve($definition), $partition, [
            'frame' => $frame,
            'partition' => $partition,
        ]);
    }

    /**
     * @param parameters $definitions
     *
     * @return parameters
     */
    private function columnDefinitions(array $definitions): array
    {
        $normalizedDefinitions = [];

        foreach ($definitions as $colName => $definition) {
            if (is_string($definition)) {
                $normalizedDefinitions[$colName] = [
                    'type' => 'expression',
                    'expression' => $definition,
                    'name' => $colName,
                ];

                continue;
            }

            if (is_array($definition)) {
                $normalizedDefinitions[$colName] = $definition;

                continue;
            }

            throw new RuntimeException(sprintf(
                'Got "%s" as type for expression, but must either be a string or a column defintion',
                gettype($definition)
            ));
        }

        return $normalizedDefinitions;
    }

    /**
     * @param array<string, array<int,mixed>> $colDefs
     * @param array<string,array{cols: string[]}> $groupDefs
     *
     * @return TableColumnGroup[]
     */
    private function resolveGroups(array $colDefs, array $groupDefs): array
    {
        $groupsByColumn = $this->groupsByColumn($groupDefs);

        $colSizes = array_map(function (array $def) {
            return count($def);
        }, $colDefs);

        return array_map(function (array $groupSize) {
            return new TableColumnGroup($groupSize[0], $groupSize[1]);
        }, GroupHelper::resolveGroupSizes($colSizes, $groupsByColumn));
    }

    /**
     * @param array<string,array{cols: string[]}> $groupDefs
     *
     * @return array<string, string>
     */
    private function groupsByColumn(array $groupDefs): array
    {
        $groupsByColName = [];

        foreach ($groupDefs as $id => $groupDef) {
            foreach ($groupDef['cols'] as $colName) {
                $groupsByColName[$colName] = $id;
            }
        }

        return $groupsByColName;
    }
}
