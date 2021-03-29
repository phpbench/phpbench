<?php

namespace PhpBench\Report\Generator;

use function array_combine;
use function array_key_exists;
use Generator;
use function iterator_to_array;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\Exception\EvaluationError;
use PhpBench\Expression\ExpressionLanguage;
use PhpBench\Expression\Printer;
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

class ExpressionGenerator implements GeneratorInterface
{
    /**
     * @var ExpressionLanguage
     */
    private $parser;

    /**
     * @var Evaluator
     */
    private $evaluator;

    /**
     * @var Printer
     */
    private $printer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SuiteCollectionTransformer
     */
    private $transformer;

    public function __construct(
        ExpressionLanguage $parser,
        Evaluator $evaluator,
        Printer $printer,
        SuiteCollectionTransformer $transformer,
        LoggerInterface $logger
    ) {
        $this->parser = $parser;
        $this->evaluator = $evaluator;
        $this->printer = $printer;
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
        "microseconds"
    ), 
    first(subject_time_precision), 
    first(subject_time_mode))
EOT
            , $expr);
        };

        $options->setDefaults([
            'title' => null,
            'description' => null,
            'cols' => [
                'benchmark',
                'subject',
                'set',
                'revs',
                'its',
                'mem_peak',
                'mode',
                'rstdev',
            ],
            'expressions' => [],
            'baseline_expressions' => [],
            'aggregate' => ['benchmark_class', 'subject_name', 'variant_name'],
            'break' => [],
        ]);

        $options->setAllowedTypes('title', ['null', 'string']);
        $options->setAllowedTypes('description', ['null', 'string']);
        $options->setAllowedTypes('cols', 'array');
        $options->setAllowedTypes('expressions', 'array');
        $options->setAllowedTypes('baseline_expressions', 'array');
        $options->setAllowedTypes('aggregate', 'array');
        $options->setAllowedTypes('break', 'array');
        $options->setNormalizer('expressions', function (Options $options, array $expressions) use ($formatTime) {
            return array_merge([
                'benchmark' => 'first(benchmark_name)',
                'subject' => 'first(subject_name)',
                'set' => 'first(variant_name)',
                'revs' => 'first(variant_revs)',
                'its' => 'first(variant_iterations)',
                'mem_peak' => 'max(result_mem_peak) as bytes',
                'best' => $formatTime('min(result_time_avg)'),
                'mode' => $formatTime('mode(result_time_avg)'),
                'mean' => $formatTime('mean(result_time_avg)'),
                'worst' => $formatTime('max(result_time_avg)'),
                'rstdev' => 'format("%.2f%%", rstdev(result_time_avg))',
            ], $expressions);
        });
        $options->setNormalizer('baseline_expressions', function (Options $options, array $expressions) use ($formatTime) {
            return array_merge([
                'best' => $formatTime('min(result_time_avg)'),
                'worst' => $formatTime('max(result_time_avg)'),
                'mode' => $formatTime('mode(result_time_avg)') . ' ~" "~ percent_diff(mode(baseline_time_avg), mode(result_time_avg), rstdev(result_time_avg))',
                'mem_peak' => '(first(baseline_mem_peak) as bytes) ~ " " ~ percent_diff(first(baseline_mem_peak), first(result_mem_peak))',
                'rstdev' => 'format("%.2f%%", rstdev(result_time_avg)) ~ " " ~ percent_diff(first(baseline_time_avg), first(result_time_avg)) ',
            ], $expressions);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function generate(SuiteCollection $collection, Config $config): Reports
    {
        $expressionMap = $this->resolveExpressionMap($config);
        $baselineExpressionMap = $this->resolveBaselineExpressionMap($config, array_keys($expressionMap));

        $table = $this->transformer->suiteToTable($collection);
        $table = $this->aggregate($table, $config['aggregate']);
        $table = iterator_to_array($this->evaluate($table, $expressionMap, $baselineExpressionMap));
        $tables = $this->partition($table, $config['break']);

        return $this->generateReports($tables, $config);
    }

    /**
     * @param array<string,mixed> $table
     * @param string[] $aggregateCols
     *
     * @return array<string,mixed>
     */
    private function aggregate(array $table, array $aggregateCols): array
    {
        $aggregated = [];

        foreach ($table as $row) {
            $hash = implode('-', array_map(function (string $key) use ($row) {
                if (!array_key_exists($key, $row)) {
                    throw new RuntimeException(sprintf(
                        'Cannot aggregate: field "%s" does not exist, know fields: "%s"',
                        $key, implode('", "', array_keys($row))
                    ));
                }

                return $row[$key];
            }, $aggregateCols));

            $aggregated[$hash] = (function () use ($row, $hash, $aggregated) {
                if (!isset($aggregated[$hash])) {
                    return array_map(function ($value) {
                        if (is_array($value)) {
                            return $value;
                        }

                        return [$value];
                    }, $row);
                }

                return array_combine(array_keys($aggregated[$hash]), array_map(function ($aggValue, $value) {
                    return array_merge((array)$aggValue, (array)$value);
                }, $aggregated[$hash], $row));
            })();
        }

        return $aggregated;
    }

    /**
     * @param array<string,mixed> $table
     *
     * @return Generator<array<string,mixed>>
     */
    private function evaluate(array $table, array $exprMap, array $baselineExprMap): Generator
    {
        foreach ($table as $row) {
            $evaledRow = [];

            foreach (($row[SuiteCollectionTransformer::COL_HAS_BASELINE][0] ? array_merge($exprMap, $baselineExprMap) : $exprMap) as $name => $expr) {
                try {
                    $evaledRow[$name] = $this->evaluator->evaluate($this->parser->parse($expr), $row);
                } catch (EvaluationError $e) {
                    $evaledRow[$name] = new StringNode('error: ' . $expr);
                    $this->logger->error($e->getMessage());
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
            isset($config['title']) ? $config['title'] : null,
            isset($config['description']) ? $config['description'] : null
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
                        $key, get_class($value)
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
        $expressions = $config['expressions'];
        $map = [];

        foreach ($config['cols'] as $key => $expr) {
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

        foreach ($config['baseline_expressions'] as $name => $baselineExpression) {
            if (!in_array($name, $visibleCols)) {
                continue;
            }
            $map[(string)$name] = (string)$baselineExpression;
        }

        return $map;
    }
}
