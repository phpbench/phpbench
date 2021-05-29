<?php

namespace PhpBench\Report\Generator;

use PhpBench\Data\DataFrame;
use PhpBench\Data\Row;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Evaluator;
use PhpBench\Expression\ExpressionLanguage;
use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Config;
use PhpBench\Report\GeneratorInterface;
use PhpBench\Report\Model\Builder\TableBuilder;
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
}
