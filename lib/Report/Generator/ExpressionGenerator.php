<?php

namespace PhpBench\Report\Generator;

use Generator;
use PhpBench\Compat\SymfonyOptionsResolverCompat;
use PhpBench\Data\DataFrame;
use PhpBench\Data\DataFrames;
use PhpBench\Data\Row;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Exception\EvaluationError;
use PhpBench\Expression\ExpressionEvaluator;
use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Config;
use PhpBench\Report\GeneratorInterface;
use PhpBench\Report\Model\Report;
use PhpBench\Report\Model\Reports;
use PhpBench\Report\Model\Table;
use PhpBench\Report\Transform\SuiteCollectionTransformer;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_key_exists;
use function iterator_to_array;

class ExpressionGenerator implements GeneratorInterface
{
    public const PARAM_TITLE = 'title';
    public const PARAM_DESCRIPTION = 'description';
    public const PARAM_COLS = 'cols';
    public const PARAM_EXPRESSIONS = 'expressions';
    public const PARAM_BASELINE_EXPRESSIONS = 'baseline_expressions';
    public const PARAM_AGGREGATE = 'aggregate';
    public const PARAM_BREAK = 'break';
    public const PARAM_INCLUDE_BASELINE = 'include_baseline';

    /**
     * @var ExpressionEvaluator
     */
    private $evaluator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SuiteCollectionTransformer
     */
    private $transformer;

    public function __construct(
        ExpressionEvaluator $evaluator,
        SuiteCollectionTransformer $transformer,
        LoggerInterface $logger
    ) {
        $this->evaluator = $evaluator;
        $this->logger = $logger;
        $this->transformer = $transformer;
    }

    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $options): void
    {
        $formatTime = function (string $expr) {
            return sprintf(<<<'EOT'
display_as_time(
    %s, 
    coalesce(
        first(subject_time_unit),
        "time"
    ), 
    first(subject_time_precision), 
    first(subject_time_mode))
EOT
                , $expr);
        };

        $options->setDefaults([
            self::PARAM_TITLE => null,
            self::PARAM_DESCRIPTION => null,
            self::PARAM_COLS => null,
            self::PARAM_EXPRESSIONS => [],
            self::PARAM_BASELINE_EXPRESSIONS => [],
            self::PARAM_AGGREGATE => ['suite_tag', 'benchmark_class', 'subject_name', 'variant_index'],
            self::PARAM_BREAK => [],
            self::PARAM_INCLUDE_BASELINE => false,
        ]);

        $options->setAllowedTypes(self::PARAM_TITLE, ['null', 'string']);
        $options->setAllowedTypes(self::PARAM_DESCRIPTION, ['null', 'string']);
        $options->setAllowedTypes(self::PARAM_COLS, ['array', 'null']);
        $options->setAllowedTypes(self::PARAM_EXPRESSIONS, 'array');
        $options->setAllowedTypes(self::PARAM_BASELINE_EXPRESSIONS, 'array');
        $options->setAllowedTypes(self::PARAM_AGGREGATE, 'array');
        $options->setAllowedTypes(self::PARAM_BREAK, 'array');
        $options->setAllowedTypes(self::PARAM_INCLUDE_BASELINE, 'bool');
        $options->setNormalizer(self::PARAM_EXPRESSIONS, function (Options $options, array $expressions) use ($formatTime) {
            return array_merge([
                'tag' => 'first(suite_tag)',
                'benchmark' => 'first(benchmark_name)',
                'subject' => 'first(subject_name)',
                'set' => 'first(variant_name)',
                'revs' => 'first(variant_revs)',
                'its' => 'first(variant_iterations)',
                'mem_peak' => 'max(result_mem_peak) as memory',
                'best' => $formatTime('min(result_time_avg)'),
                'mode' => $formatTime('mode(result_time_avg)'),
                'mean' => $formatTime('mean(result_time_avg)'),
                'worst' => $formatTime('max(result_time_avg)'),
                'stdev' => $formatTime('stdev(result_time_avg)'),
                'rstdev' => 'rstdev(result_time_avg)',
            ], $expressions);
        });
        $options->setNormalizer(self::PARAM_BASELINE_EXPRESSIONS, function (Options $options, array $expressions) use ($formatTime) {
            return array_merge([
                'best' => $formatTime('min(result_time_avg)'),
                'worst' => $formatTime('max(result_time_avg)'),
                'mode' => $formatTime('mode(result_time_avg)') . ' ~" "~ percent_diff(mode(baseline_time_avg), mode(result_time_avg), rstdev(result_time_avg))',
                'mem_peak' => '(first(baseline_mem_peak) as memory) ~ " " ~ percent_diff(first(baseline_mem_peak), first(result_mem_peak))',
                'rstdev' => 'rstdev(result_time_avg) ~ " " ~ percent_diff(rstdev(baseline_time_avg), rstdev(result_time_avg))',
            ], $expressions);
        });
        $options->setNormalizer(self::PARAM_COLS, function (Options $options, ?array $cols) {
            if (null !== $cols) {
                return $cols;
            }

            return array_keys($options[self::PARAM_EXPRESSIONS]);
        });
        SymfonyOptionsResolverCompat::setInfos($options, [
            self::PARAM_TITLE => 'Title to use for report',
            self::PARAM_DESCRIPTION => 'Description to use for report',
            self::PARAM_COLS => 'Columns to display',
            self::PARAM_EXPRESSIONS => 'Map from column names to expressions',
            self::PARAM_BASELINE_EXPRESSIONS => 'When the baseline is used, expressions here will be merged with the ``expressions``.',
            self::PARAM_AGGREGATE => 'Group rows by these columns',
            self::PARAM_BREAK => 'Group tables by these columns',
            self::PARAM_INCLUDE_BASELINE => 'If the baseline should be included as additional rows, or if it should be inlined',
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function generate(SuiteCollection $collection, Config $config): Reports
    {
        $expressionMap = $this->resolveExpressionMap($config);
        $baselineExpressionMap = $this->resolveBaselineExpressionMap($config, array_keys($expressionMap));

        // transform the suite into a data frame
        $frame = $this->transformer->suiteToFrame($collection->firstOnly(), $config[self::PARAM_INCLUDE_BASELINE]);
        // parition the data frame into data frames grouped by aggregates
        $frames = $frame->partition(function (Row $row) use ($config) {
            return implode('-', array_map(function (string $column) use ($row) {
                return $row[$column];
            }, $config[self::PARAM_AGGREGATE]));
        });

        // evaluate the aggregated values with the expression language - at
        // this point the data frame is coverted to an array with Node
        // instances rather than scalar values
        $table = iterator_to_array($this->evaluate($frame, $frames, $expressionMap, $baselineExpressionMap));

        // split the evaluated tables by "break" for display
        $tables = $this->partition($table, $config[self::PARAM_BREAK]);

        // convert the array into Table instances for rendering
        return $this->generateReports($tables, $config);
    }

    /**
     *
     * @return Generator<array<string,mixed>>
     */
    private function evaluate(DataFrame $allFrame, DataFrames $frames, array $exprMap, array $baselineExprMap): Generator
    {
        $hash = 0;

        foreach ($frames as $frame) {
            assert($frame instanceof DataFrame);
            $evaledRow = [];

            $exprMap = $frame->row(0)->get(SuiteCollectionTransformer::COL_HAS_BASELINE) ? array_merge(
                $exprMap,
                $baselineExprMap
            ) : $exprMap;

            $columnValues = array_merge(
                [
                    'suite' => $allFrame
                ],
                $frame->nonNullColumnValues()
            );

            foreach ($exprMap as $name => $expr) {
                try {
                    $evaledRow[$name] = $this->evaluator->evaluate(
                        $expr,
                        $columnValues
                    );
                } catch (EvaluationError $e) {
                    $evaledRow[$name] = new StringNode('ERR');
                    $this->logger->error(sprintf(
                        'Expression error (column "%s"): %s %s',
                        $name,
                        $e->getMessage(),
                        $e->getTraceAsString()
                    ));
                }
            }

            yield $evaledRow;
        }
    }

    /**
     * @param array<string,array<int,array<string,Node>>> $tables
     */
    private function generateReports(array $tables, Config $config): Reports
    {
        return Reports::fromReport(Report::fromTables(
            array_map(function (array $table, string $title) {
                return Table::fromRowArray($table, $title);
            }, $tables, array_keys($tables)),
            isset($config[self::PARAM_TITLE]) ? $config[self::PARAM_TITLE] : null,
            isset($config[self::PARAM_DESCRIPTION]) ? $config[self::PARAM_DESCRIPTION] : null
        ));
    }

    /**
     * @param array<string,array<string,Node>> $table
     * @param string[] $breakCols
     *
     * @return array<string,array<int,array<string,Node>>>
     */
    private function partition(array $table, array $breakCols): array
    {
        $partitioned = [];

        foreach ($table as $key => $row) {
            $hash = implode('-', array_map(function (string $key) use ($row) {
                if (!array_key_exists($key, $row)) {
                    throw new RuntimeException(sprintf(
                        'Cannot partition table: column "%s" does not exist, known columns: "%s"',
                        $key,
                        implode('", "', array_keys($row))
                    ));
                }

                $value = $row[$key];

                if (!$value instanceof StringNode) {
                    throw new RuntimeException(sprintf(
                        'Partition value for "%s" must be a string, got "%s"',
                        $key,
                        get_class($value)
                    ));
                }

                return $value->value();
            }, $breakCols));

            foreach ($breakCols as $col) {
                unset($row[$col]);
            }

            if (!isset($partitioned[$hash])) {
                $partitioned[$hash] = [];
            }

            $partitioned[$hash][] = $row;
        }

        return $partitioned;
    }

    /**
     * @return array<string,string>
     */
    private function resolveExpressionMap(Config $config): array
    {
        $expressions = $config[self::PARAM_EXPRESSIONS];
        $map = [];

        foreach ($config[self::PARAM_COLS] as $key => $expr) {
            if (is_int($key) || null === $expr) {
                $expr = null === $expr ? $key : $expr;

                if (!isset($expressions[$expr])) {
                    throw new RuntimeException(sprintf(
                        'No expression with name "%s" is available, available expressions: "%s"',
                        $expr,
                        implode('", "', array_keys($expressions))
                    ));
                }
                $map[(string)$expr] = (string)$expressions[$expr];

                continue;
            }
            $map[(string)$key] = (string)$expr;
        }

        return $map;
    }

    /**
     * @param string[] $visibleCols
     *
     * @return array<string,string>
     */
    private function resolveBaselineExpressionMap(Config $config, array $visibleCols): array
    {
        $map = [];

        foreach ($config[self::PARAM_BASELINE_EXPRESSIONS] as $name => $baselineExpression) {
            if (!in_array($name, $visibleCols)) {
                continue;
            }
            $map[(string)$name] = (string)$baselineExpression;
        }

        return $map;
    }
}
