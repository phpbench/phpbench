<?php

namespace PhpBench\Report\Generator;

use function array_combine;
use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\DisplayAsNode;
use PhpBench\Expression\Ast\FloatNode;
use PhpBench\Expression\Ast\IntegerNode;
use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\NullNode;
use PhpBench\Expression\Ast\ParameterNode;
use PhpBench\Expression\Ast\PercentDifferenceNode;
use PhpBench\Expression\Ast\RelativeDeviationNode;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Ast\UnitNode;
use PhpBench\Expression\Ast\VariableNode;
use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Config;
use PhpBench\Report\GeneratorInterface;
use PhpBench\Report\Model\BarChart;
use PhpBench\Report\Model\BarChartDataSet;
use PhpBench\Report\Model\Builder\ReportBuilder;
use PhpBench\Report\Model\Reports;
use PhpBench\Report\Model\Table;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OutputTestGenerator implements GeneratorInterface
{
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
        $range = range(1, 128);
        $builder = ReportBuilder::create('Output Test')
            ->withDescription('This report demonstrates output')
            ->addObject(
                Table::fromRowArray([
                        [
                            'string' => new StringNode('one'),
                            'int' => new IntegerNode(123),
                            'float' => new FloatNode(12.2),
                            'bool' => new ListNode([new BooleanNode(true), new BooleanNode(false)]),
                            'null' => new NullNode(),
                            'parameter' => new ParameterNode([new VariableNode('foo'), new VariableNode('bar')]),
                        ]
                ], 'Values')
            )
            ->addObject(
                Table::fromRowArray([
                    (function (array $percents): array {
                        return (array)array_combine($percents, array_map(function (float $percent) {
                            return new PercentDifferenceNode($percent);
                        }, $percents));
                    })(range(-100, 100, 10)),
                ], 'Percent Difference')
            )
            ->addObject(
                Table::fromRowArray([
                    (function (array $percents): array {
                        return (array)array_combine($percents, array_map(function (float $percent) {
                            return new RelativeDeviationNode(new FloatNode($percent));
                        }, $percents));
                    })(range(0, 100, 10)),
                ], 'Relative Deviation'),
            )
            ->addObject(
                Table::fromRowArray([
                    [
                        'time' => new DisplayAsNode(new IntegerNode(10000), new UnitNode(new StringNode('ms')), new IntegerNode(1))
                    ]
                ], 'Time')
            )
                ->addObject(
                    new BarChart([
                        new BarChartDataSet('Set 1', $range, $range, $range),
                        new BarChartDataSet('Set 2', $range, $range, $range),
                    ],'Example Aggregate Barchart')
                );

        return Reports::fromReport($builder->build());
    }
}
