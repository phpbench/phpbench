<?php

namespace PhpBench\Tests\Benchmark\Report\Generator;

use PhpBench\Model\Suite;
use PhpBench\Model\SuiteCollection;
use PhpBench\Model\Variant;
use PhpBench\Registry\Config;
use PhpBench\Report\Generator\ExpressionGenerator;
use PhpBench\Tests\Benchmark\IntegrationBenchCase;
use PhpBench\Tests\Util\TestUtil;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @OutputTimeUnit("milliseconds")
 * @Assert("mode(variant.time.avg) as ms <= mode(baseline.time.avg) as ms +/- 5%")
 * @Iterations(10)
 */
class ExpressionGeneratorBench extends IntegrationBenchCase
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
        $this->generator = $this->container()->get(ExpressionGenerator::class);
        $this->suite = TestUtil::createSuite([
            'benchmarks' => ['benchOne', 'benchTwo'],
            'subjects' => [
                'one', 'two',
            ],
            'iterations' => array_fill(0, 100, 10),
        ]);
        $options = new OptionsResolver();
        $this->generator->configure($options);
        $this->config = $options->resolve();
    }

    public function benchGenerate(): void
    {
        $this->generator->generate(new SuiteCollection([$this->suite]), new Config('foo', $this->config));
    }
}
