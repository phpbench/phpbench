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
use PhpBench\Model\Iteration;
use PhpBench\Model\Suite;
use PhpBench\Model\SuiteCollection;
use PhpBench\Model\Variant;
use PhpBench\Registry\Config;
use PhpBench\Report\GeneratorInterface;
use PhpBench\Report\Model\Report;
use PhpBench\Report\Model\Reports;
use PhpBench\Report\Model\Table;
use Psr\Log\LoggerInterface;
use RuntimeException;
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

    public function __construct(
        ExpressionLanguage $parser,
        Evaluator $evaluator,
        Printer $printer,
        LoggerInterface $logger
    ) {
        $this->parser = $parser;
        $this->evaluator = $evaluator;
        $this->printer = $printer;
        $this->logger = $logger;
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
                'benchmark' => 'first(benchmark_class)',
                'subject' => 'first(subject_name)',
                'set' => 'first(variant_name)',
                'revs' => 'first(variant_revs)',
                'its' => 'first(variant_iterations)',
                'mem_peak' => 'max(result_mem_peak) as bytes',
                'best' => $formatTime('min(result_time_avg)'),
                'mode' => $formatTime('mode(result_time_avg)'),
                'worst' => $formatTime('max(result_time_avg)'),
                'rstdev' => 'format("%.2f%%", rstdev(result_time_avg))',
            ],
            'baseline_cols' => [
                'best' => $formatTime('min(result_time_avg)') . ' ~" Dif"~ percent_diff(min(baseline_time_avg), min(result_time_avg), 100)',
                'worst' => $formatTime('max(result_time_avg)') . ' ~" Dif"~ percent_diff(max(baseline_time_avg), max(result_time_avg), 100)',
                'mode' => $formatTime('mode(result_time_avg)') . ' ~" Dif"~ percent_diff(mode(baseline_time_avg), mode(result_time_avg), rstdev(result_time_avg))',
                'mem_peak' => '(first(baseline_mem_peak) as bytes), percent_diff(first(baseline_mem_peak), first(result_mem_peak))',
            ],
            'aggregate' => ['benchmark_class', 'subject_name', 'variant_name'],
            'break' => ['subject','benchmark'],
        ]);

        $options->setAllowedTypes('title', ['null', 'string']);
        $options->setAllowedTypes('description', ['null', 'string']);
        $options->setAllowedTypes('cols', 'array');
        $options->setAllowedTypes('aggregate', 'array');
        $options->setAllowedTypes('break', 'array');
    }

    /**
     * {@inheritDoc}
     */
    public function generate(SuiteCollection $collection, Config $config): Reports
    {
        $table = iterator_to_array($this->reportData($collection));
        $table = $this->normalize($table);
        $table = $this->aggregate($table, $config['aggregate']);
        $table = iterator_to_array($this->evaluate($table, $config['cols'], $config['baseline_cols']));
        $tables = $this->partition($table, $config['break']);

        return $this->generateReports($tables, $config);
    }

    /**
     * @return Generator<array<string, mixed>>
     */
    private function reportData(SuiteCollection $collection): Generator
    {
        foreach ($collection as $suite) {
            assert($suite instanceof Suite);

            foreach ($suite->getSubjects() as $subject) {
                foreach ($subject->getVariants() as $variant) {
                    $nbIterations = (function (Variant $variant, ?Variant $baseline) {
                        if (null === $baseline) {
                            return count($variant->getIterations());
                        }

                        return max(count($variant->getIterations()), count($baseline->getIterations()));
                    })($variant, $variant->getBaseline());

                    for ($itNum = 0; $itNum < $nbIterations; $itNum++) {
                        $iteration = $variant->getIteration($itNum);
                        $baseline = $variant->getBaseline();
                        $baselineIteration = $baseline ? $baseline->getIteration($itNum) : null;

                        yield array_merge([
                            'baseline' => $baseline ? true : false,
                            'benchmark_class' => $subject->getBenchmark()->getClass(),
                            'subject_name' => $subject->getName(),
                            'subject_groups' => $subject->getGroups(),
                            'subject_time_unit' => $subject->getOutputTimeUnit(),
                            'subject_time_precision' => $subject->getOutputTimePrecision(),
                            'subject_time_mode' => $subject->getOutputMode(),
                            'variant_name' => $variant->getParameterSet()->getName(),
                            'variant_params' => $variant->getParameterSet()->getArrayCopy(),
                            'variant_revs' => $variant->getRevolutions(),
                            'variant_iterations' => count($variant->getIterations()),
                            'suite_tag' => $suite->getTag() ? $suite->getTag()->__toString() : '<current>',
                            'suite_date' => $suite->getDate()->format('c'),
                            'iteration_index' => $itNum,
                        ], $this->resultData($iteration, 'result'), $this->resultData($baselineIteration, 'baseline'));
                    }
                }
            }
        }
    }

    /**
     * @return array<string,mixed>
     */
    private function resultData(?Iteration $iteration, string $prefix = 'result'): array
    {
        if (null === $iteration) {
            return [];
        }

        $data = [];

        foreach ($iteration->getResults() as $result) {
            foreach ($result->getMetrics() as $key => $value) {
                $data[sprintf('%s_%s_%s', $prefix, $result->getKey(), $key)] = $value;
            }
        }

        return $data;
    }


    /**
     * @param array<string,mixed> $table
     *
     * @return array<string,mixed>
     */
    private function normalize(array $table): array
    {
        $cols = [];

        foreach ($table as $row) {
            foreach ($row as $key => $value) {
                if (!isset($cols[$key])) {
                    $cols[$key] = null;
                }
            }
        }

        foreach ($table as &$row) {
            $row = array_merge($cols, $row);
        }

        return $table;
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
     * @param array<string,string> $cols
     * @param array<string,string> $baselineCols
     *
     * @return Generator<array<string,mixed>>
     */
    private function evaluate(array $table, array $cols, array $baselineCols): Generator
    {
        foreach ($table as $row) {
            $evaledRow = [];

            foreach (($row['baseline'][0] ? array_merge($cols, $baselineCols) : $cols) as $name => $expr) {
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
        return Reports::fromReport(new Report(
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
}
