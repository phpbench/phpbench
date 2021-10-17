<?php

namespace PhpBench\Report\ComponentGenerator;

use PhpBench\Compat\SymfonyOptionsResolverCompat;
use PhpBench\Data\DataFrame;
use PhpBench\Report\Bridge\ExpressionBridge;
use PhpBench\Report\ComponentGeneratorInterface;
use PhpBench\Report\ComponentGenerator\TableAggregate\ColumnProcessorInterface;
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
            self::PARAM_GROUPS => 'Group columns together',
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function generateComponent(DataFrame $dataFrame, array $config): ComponentInterface
    {
        $columnDefinitions = $this->columnDefinitions($config[self::PARAM_ROW]);
        $expanded = [];

        $rows = [];
        foreach ($this->evaluator->partition($dataFrame, (array)$config[self::PARAM_PARTITION]) as $dataFrameRow) {
            $row = [];
            foreach ($columnDefinitions as $colName => $definition) {
                $newRow = $this->processColumnDefinition($row, $definition, $dataFrameRow, $dataFrame);
                if (!isset($expanded[$colName])) {
                    $expanded[$colName] = array_diff(array_keys($newRow), array_keys($row));
                }
                $row = $newRow;
            }
            $rows[] = $row;
        }

        $builder = TableBuilder::create()
            ->addRowsFromArray($rows)
            ->addGroups($this->resolveGroups($expanded, $config['groups']));

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

    /**
     * @param parameters $definitions
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
     * @return TableColumnGroup[]
     */
    private function resolveGroups(array $expanded, array $groupDefinitions): array
    {
        $groupsByColumn = $this->groupsByColumn($groupDefinitions);
        $lastGroup = null;
        $resolvedGroups = [];
        $colBuffer = [];

        foreach ($expanded as $originalColName => $colNames) {
            $definition = $groupsByColumn[$originalColName] ?? null;
            $groupName = $definition ? $definition['id'] : 'default';

            if (null === $lastGroup) {
                $lastGroup = $groupName;
                $colBuffer = $colNames;
                continue;
            }

            if ($lastGroup === $groupName) {
                $colBuffer = array_merge($colBuffer, $colNames);
                continue;
            }

            $resolvedGroups[] = new TableColumnGroup($lastGroup, count($colBuffer));
            $colBuffer = $colNames;
            $lastGroup = $groupName;
        }

        if (count($colBuffer)) {
            $resolvedGroups[] = new TableColumnGroup($lastGroup, count($colBuffer));
        }

        return $resolvedGroups;
    }

    private function groupsByColumn(array $groupDefinitions): array
    {
        $d = [];
        foreach ($groupDefinitions as $id => $definition) {
            $definition['id'] = $id;
            foreach ($definition['cols'] as $col) {
                $d[$col] = $definition;
            }
        }

        return $d;
    }
}
