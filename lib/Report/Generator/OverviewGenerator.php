<?php

namespace PhpBench\Report\Generator;

use Generator;
use PhpBench\Data\DataFrame;
use PhpBench\Data\Row;
use PhpBench\Expression\Ast\FloatNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\ExpressionLanguage;
use PhpBench\Math\Statistics;
use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Config;
use PhpBench\Report\GeneratorInterface;
use PhpBench\Report\Model\BarChart;
use PhpBench\Report\Model\Builder\ReportBuilder;
use PhpBench\Report\Model\Builder\TableBuilder;
use PhpBench\Report\Model\ChartData;
use PhpBench\Report\Model\ChartSeries;
use PhpBench\Report\Model\Report;
use PhpBench\Report\Model\Reports;
use PhpBench\Report\Model\Table;
use PhpBench\Report\Model\TableRow;
use PhpBench\Report\Transform\SuiteCollectionTransformer;
use PhpBench\Util\TimeUnit;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OverviewGenerator implements GeneratorInterface
{
    /**
     * @var SuiteCollectionTransformer
     */
    private $transformer;

    /**
     * @var ExpressionLanguage
     */
    private $language;

    /**
     * @var Evaluator
     */
    private $evaluator;

    /**
     * @var Evaluator
     */
    private $evaluato;

    public function __construct(
        SuiteCollectionTransformer $transformer,
        ExpressionLanguage $language,
        Evaluator $evaluato
    )
    {
        $this->transformer = $transformer;
        $this->language = $language;
        $this->evaluator = $evaluato;
    }

    /**
     * {@inheritDoc}
     */
    public function configure(OptionsResolver $options): void
    {
    }

    /**
     * {@inheritDoc}
     */
    public function generate(SuiteCollection $collection, Config $config): Reports
    {
        $frame = $this->transformer->suiteToFrame($collection);
        $reports[] = $this->buildSummary($frame);
        $reports[] = $this->comparisonChart($frame);
        foreach (iterator_to_array($this->benchmarkReports($frame)) as $report) {
            $reports[] = $report;
        }

        return Reports::fromReports(...$reports);
    }

    private function buildSummary(DataFrame $frame): Report
    {
        $tables = [];
        $tables[] = TableBuilder::create()
            ->addRowArray([
                'nb. benchmarks',
                $frame->partition(['benchmark_name'])->count(),
                'total time',
                $this->evaluator->evaluate(
                    $this->language->parse('display_as_time(value, "time")'),
                    ['value' => $frame->column('result_time_net')->sum()]
                )
            ])
            ->addRowArray([
                'nb. subjects',
                $frame->partition(['benchmark_name', 'subject_name'])->count(),
                '',''
            ])
            ->addRowArray([
                'nb. variants',
                $frame->partition(['benchmark_name', 'subject_name', 'variant_name'])->count(),
                '',
                ''
            ])
            ->addRowArray([
                'nb. iterations',
                count($frame->rows()),
                '',
                ''
            ])
            ->addRowArray([
                'nb. revs',
                $frame->column('result_time_revs')->sum(),
                '',
                ''
            ])
            ->build();

        return Report::fromTables($tables, 'Summary');
    }

    private function comparisonChart(DataFrame $frame): Report
    {
        $labels = [];
        $data = [];
        foreach ($frame->partition(['benchmark_name']) as $label => $suite) {
            $labels[] = $label;
            foreach ($suite->partition(['suite_tag']) as $tag => $benchmark) {
                $series[$tag][] = $benchmark->column('result_time_net')->sum();
            }
        }

        return ReportBuilder::create('Overview')
            ->addObject(new BarChart(
                new ChartSeries($labels),
                new ChartData(array_combine(array_keys($series), array_map(function (array $series) {
                    return new ChartSeries($series);
                }, $series)))
            ))
            ->build();
    }

    private function benchmarkReports(DataFrame $frame): Generator
    {
        foreach ($frame->partition(['benchmark_name']) as $label => $benchmark) {
            yield $this->benchmarkReport($label, $benchmark);
        }
    }

    private function benchmarkReport(string $benchmarkClass, DataFrame $benchmark): Report
    {
        $labels = [];
        $data = [];
        $series = [];
        $errorMargins = [];
        foreach ($benchmark->partition(['subject_name', 'variant_name']) as $label => $suite) {
            $labels[] = $label;
            foreach ($suite->partition(['suite_tag']) as $tag => $suiteVariant) {
                $series[$tag][] = Statistics::kdeMode($suiteVariant->column('result_time_avg')->toValues());
                $errorMargins[$tag][] = Statistics::stdev($suiteVariant->column('result_time_avg')->toValues());
            }
        }

        return ReportBuilder::create($benchmarkClass)
            ->addObject(new BarChart(
                new ChartSeries($labels),
                new ChartData(array_combine(array_keys($series), array_map(function (array $series, $errorMargins) {
                    return new ChartSeries($series, $errorMargins);
                }, $series, $errorMargins)))
            ))
            ->build();
    }
}
