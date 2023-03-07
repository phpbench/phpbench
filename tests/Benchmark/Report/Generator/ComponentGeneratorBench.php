<?php

namespace PhpBench\Tests\Benchmark\Report\Generator;

use Generator;
use PhpBench\Model\Suite;
use PhpBench\Model\SuiteCollection;
use PhpBench\Model\Variant;
use PhpBench\Registry\Config;
use PhpBench\Report\Generator\ComponentGenerator;
use PhpBench\Report\Generator\ExpressionGenerator;
use PhpBench\Tests\Benchmark\IntegrationBenchCase;
use PhpBench\Tests\Util\TestUtil;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_fill;

/**
 * @Assert("mode(variant.time.avg) as time <= mode(baseline.time.avg) as time +/- 5%")
 */
class ComponentGeneratorBench extends IntegrationBenchCase
{
    /**
     * @var ExpressionGenerator
     */
    private $generator;

    /**
     * @var Suite
     */
    private $suite;

    /**
     * @var OptionsResolver
     */
    private $config;

    public function __construct()
    {
        $this->generator = $this->container()->get(ComponentGenerator::class);
    }

    /**
     * @param parameters $config
     */
    public function prepare(array $config): void
    {
        $this->suite = TestUtil::createSuite([
            'benchmarks' => [
                'bench1',
                'bench2',
                'bench3',
                'bench4',
            ],
            'subjects' => [
                'one', 'two',
            ],
            'iterations' => array_fill(0, $config['nb_iterations'], 10),
        ]);
        $options = new OptionsResolver();
        $this->generator->configure($options);
        $this->config = $options->resolve($config['config']);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideConfigs(): Generator
    {
        yield 'text' => [
            'config' => [
                'components' => [
                    [
                        'component' => 'text',
                        'text' => 'Helo World'
                    ]
                ]
            ]
        ];

        yield 'bar_chart_aggregate' => [
            'config' => [
                'components' => [
                    [
                        'component' => 'bar_chart_aggregate',
                        "y_expr" => "mode(partition[\"result_time_avg\"])",
                        "y_error_margin" => "stdev(partition[\"result_time_avg\"])",
                        "y_axes_label" => "yValue as time precision 1"
                    ]
                ]
            ]
        ];

        yield 'table_aggregate' => [
            'config' => [
                'components' => [
                    [
                        "component" => "table_aggregate",
                        "title" => "{{ first(frame.suite_tag) }}",
                        "partition" => ["benchmark_name", "subject_name", "variant_name"],
                        "row" => [
                            "benchmark" => "first(partition[\"benchmark_name\"])",
                            "subject" => "first(partition[\"subject_name\"]) ~ \" (\" ~ first(partition[\"variant_name\"]) ~ \")\"",
                            "memory" => "first(partition[\"result_mem_peak\"]) as memory",
                            "min" => "min(partition[\"result_time_avg\"]) as time",
                            "max" => "max(partition[\"result_time_avg\"]) as time",
                            "mode" => "mode(partition[\"result_time_avg\"]) as time",
                            "rstdev" => "rstdev(partition[\"result_time_avg\"])",
                            "stdev" => "stdev(partition[\"result_time_avg\"]) as time"
                        ]
                    ]
                ]
            ]
        ];
    }

    public function provideNumberOfIterations(): Generator
    {
        for ($i = 0; $i < 100; $i += 25) {
            yield $i => [
                'nb_iterations' => $i
            ];
        }
    }

    /**
     * @BeforeMethods({"prepare"})
     *
     * @ParamProviders({"provideConfigs", "provideNumberOfIterations"})
     *
     * @Revs(2)
     */
    public function benchGenerate(): void
    {
        $this->generator->generate(new SuiteCollection([
            $this->suite
        ]), new Config('foo', $this->config));
    }
}
