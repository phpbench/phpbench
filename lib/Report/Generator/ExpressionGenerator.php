<?php

namespace PhpBench\Report\Generator;

use Generator;
use PhpBench\Dom\Document;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\ExpressionLanguage;
use PhpBench\Expression\Printer;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Iteration;
use PhpBench\Model\ResultInterface;
use PhpBench\Model\Subject;
use PhpBench\Model\Suite;
use PhpBench\Model\SuiteCollection;
use PhpBench\Model\Variant;
use PhpBench\Registry\Config;
use PhpBench\Report\GeneratorInterface;
use PhpBench\Report\Generator\Table\Cell;
use PhpBench\Report\Generator\Table\Row;
use PhpBench\Report\Generator\Table\ValueRole;
use Symfony\Component\OptionsResolver\OptionsResolver;
use function array_combine;
use function array_reduce;
use function iterator_to_array;

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

    public function __construct(
        ExpressionLanguage $parser,
        Evaluator $evaluator,
        Printer $printer
    )
    {
        $this->parser = $parser;
        $this->evaluator = $evaluator;
        $this->printer = $printer;
    }

    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $options): void
    {
        $options->setDefaults([
            'title' => null,
            'description' => null,
            'cols' => [
                'benchmark' => 'first(benchmark_class)',
                'subject' => 'first(subject_name)',
                'tag' => 'first(suite_tag)',
                'set' => 'first(variant_name)',
                'revs' => 'first(variant_revs)',
                'its' => 'first(variant_iterations)',
                'mem_peak' => 'max(result_mem_peak) as bytes',
                'best' => 'min(result_time_avg) as time',
                'mode' => 'mode(result_time_avg) as time',
                'worst' => 'max(result_time_avg) as time',
                'rstdev' => 'format("%d.2%%", rstdev(result_time_avg))',
            ],
            'baseline_cols' => [
                'best' => '(min(baseline_time_avg) as time), percent_diff(min(baseline_time_avg), min(result_time_avg))',
                'worst' => '(max(baseline_time_avg) as time), percent_diff(max(baseline_time_avg), max(result_time_avg))',
                'mode' => '(mode(baseline_time_avg) as time), percent_diff(mode(baseline_time_avg), mode(result_time_avg))',
                'mem_peak' => '(first(baseline_mem_peak) as bytes), percent_diff(first(baseline_mem_peak), first(result_mem_peak))',
                'rstdev' => 'format("%d.2%%", rstdev(baseline_time_avg))',
            ],
            'aggregate' => ['benchmark_class', 'subject_name', 'variant_name', 'baseline'],
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
    public function generate(SuiteCollection $collection, Config $config): Document
    {
        $data = iterator_to_array($this->reportData($collection));
        $data = $this->aggregate($data, $config['aggregate']);
        $data = iterator_to_array($this->evaluate($data, $config['cols'], $config['baseline_cols']));
        $data = $this->partition($data, $config['break']);

        return $this->generateDocument($data, $config);
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
                    foreach ($variant->getIterations() as $iteration) {
                        yield array_merge([
                            'baseline' => false,
                            'benchmark_class' => $subject->getBenchmark()->getClass(),
                            'subject_name' => $subject->getName(),
                            'subject_groups' => $subject->getGroups(),
                            'variant_name' => $variant->getParameterSet()->getName(),
                            'variant_params' => $variant->getParameterSet()->getArrayCopy(),
                            'variant_revs' => $variant->getRevolutions(),
                            'variant_iterations' => count($variant->getIterations()),
                            'suite_tag' => $suite->getTag() ? $suite->getTag()->__toString() : '<current>',
                            'suite_date' => $suite->getDate()->format('c')
                        ], $this->resultData($iteration));
                    }

                    $baseline = $variant->getBaseline();

                    if (!$baseline) {
                        continue;
                    }

                    foreach ($baseline->getIterations() as $iteration) {
                        $baselineSuite = $baseline->getSubject()->getBenchmark()->getSuite();
                        $variantIteration = $variant->getIteration($iteration->getIndex()) ?? $variant->getIteration(0);
                        yield array_merge([
                            'baseline' => true,
                            'benchmark_class' => $subject->getBenchmark()->getClass(),
                            'subject_name' => $subject->getName(),
                            'subject_groups' => $subject->getGroups(),
                            'variant_name' => $baseline->getParameterSet()->getName(),
                            'variant_params' => $baseline->getParameterSet()->getArrayCopy(),
                            'variant_revs' => $baseline->getRevolutions(),
                            'variant_iterations' => count($baseline->getIterations()),
                            'suite_tag' => $baselineSuite->getTag() ? $baselineSuite->getTag()->__toString() : '',
                            'suite_date' => $baselineSuite->getDate()->format('c')
                        ], $this->resultData($variantIteration), $this->resultData($iteration, 'baseline'));
                    }
                }
            }
        }
    }

    /**
     * @return array<string,mixed>
     */
    private function resultData(Iteration $iteration, string $prefix = 'result'): array
    {
        $data = [];
        foreach ($iteration->getResults() as $result) {
            foreach ($result->getMetrics() as $key => $value) {
                $data[sprintf('%s_%s_%s', $prefix, $result->getKey(), $key)] = $value;
            }
        }

        return $data;
    }

    /**
     * @param array<string.mixed> $data
     * @param string[] $aggregateCols
     *
     * @return array<string,mixed>
     */
    private function aggregate(array $data, array $aggregateCols): array
    {
        $aggregated = [];
        foreach ($data as $row) {
            $hash = implode('-', array_map(function (string $key) use ($row) {
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
     * @param array<string,mixed> $data
     * @param array<string,string> $cols
     * @return Generator<array<string,mixed>>
     */
    private function evaluate(array $data, array $cols, array $baselineCols): Generator
    {
        foreach ($data as $row) {
            $evaledRow = [];

            foreach (($row['baseline'][0] ? array_merge($cols, $baselineCols) : $cols) as $name => $expr) {
                $evaledRow[$name] = $this->printer->print($this->evaluator->evaluate(
                    $this->parser->parse($expr),
                    $row
                ), $row);
            }

            yield $evaledRow;
        }
    }

    private function generateDocument(array $tables, Config $config): Document
    {
        $document = new Document();
        $reportsEl = $document->createRoot('reports');
        $reportsEl->setAttribute('name', 'table');
        $reportEl = $reportsEl->appendElement('report');

        if (isset($config['title'])) {
            $reportEl->setAttribute('title', $config['title']);
        }

        if (isset($config['description'])) {
            $reportEl->appendElement('description', $config['description']);
        }

        foreach ($tables as $breakHash => $table) {
            $tableEl = $reportEl->appendElement('table');

            // Build the col(umn) definitions.
            foreach ($table as $row) {
                assert($row instanceof Row);
                $colsEl = $tableEl->appendElement('cols');

                foreach (array_keys($row) as $colName) {
                    $colEl = $colsEl->appendElement('col');
                    $colEl->setAttribute('name', $colName);

                    // column labels are the column names by default.
                    // the user may override by column name or column index.
                    $colLabel = $colName;

                    if (isset($config['labels'][$colName])) {
                        $colLabel = $config['labels'][$colName];
                    }

                    $colEl->setAttribute('label', $colLabel);
                }

                break;
            }

            if ($breakHash) {
                $tableEl->setAttribute('title', (string)$breakHash);
            }

            $groupEl = $tableEl->appendElement('group');
            $groupEl->setAttribute('name', 'body');

            foreach ($table as $row) {
                assert($row instanceof Row);
                $rowEl = $groupEl->appendElement('row');

                foreach ($row as $key => $cell) {
                    assert($cell instanceof Cell);
                    $cellEl = $rowEl->appendElement('cell');
                    $cellEl->setAttribute('name', $key);

                    $valueEl = $cellEl->appendElement('value', $cell);
                    $valueEl->setAttribute('role', 'primary');
                }
            }
        }

        return $document;
    }

    private function partition(array $data, array $breakCols): array
    {
        $partitioned = [];
        foreach ($data as $key => $row) {
            $hash = implode('-', array_map(function (string $key) use ($row) {
                return $row[$key];
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
