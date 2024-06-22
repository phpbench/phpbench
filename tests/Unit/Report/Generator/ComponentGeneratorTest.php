<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Tests\Unit\Report\Generator;

use PhpBench\DependencyInjection\Container;
use PhpBench\Extension\ReportExtension;
use PhpBench\Report\Bridge\ExpressionBridge;
use PhpBench\Report\Generator\ComponentGenerator;
use PhpBench\Report\GeneratorInterface;
use PhpBench\Report\Model\Table;
use PhpBench\Report\Transform\SuiteCollectionTransformer;
use PhpBench\Tests\Util\TestUtil;
use Psr\Log\NullLogger;

class ComponentGeneratorTest extends GeneratorTestCase
{
    protected static function acceptanceSubPath(): string
    {
        return 'component';
    }

    protected function createGenerator(Container $container): GeneratorInterface
    {
        $container = $this->container();

        return new ComponentGenerator(
            $container->get(SuiteCollectionTransformer::class),
            $container->get(ReportExtension::SERVICE_REGISTRY_COMPONENT),
            $container->get(ExpressionBridge::class),
            new NullLogger()
        );
    }

    public function testTabsAndTabLabels(): void
    {
        $generator = $this->createGenerator($this->container());
        $reports = $generator->generate(TestUtil::createCollection(), $this->resolveConfig($generator, [
            ComponentGenerator::PARAM_TABBED => true,
            ComponentGenerator::PARAM_TAB_LABELS => [ 'one', 'two' ]
        ]));
        $report = $reports->first();
        self::assertTrue($report->tabbed());
        self::assertEquals(['one', 'two'], $report->tabLabels());
    }

    public function testDefaultTabLabels(): void
    {
        $generator = $this->createGenerator($this->container());
        $reports = $generator->generate(TestUtil::createCollection([[]]), $this->resolveConfig($generator, [
            ComponentGenerator::PARAM_TABBED => true,
            ComponentGenerator::PARAM_TAB_LABELS => [ 'one' ],
            ComponentGenerator::PARAM_COMPONENTS => [
                [
                    'component' => 'table_aggregate',
                    'title' => 'hello',
                ],
                [
                    'component' => 'table_aggregate',
                    'title' => 'goodbye',
                ]
            ]
        ]));
        $report = $reports->first();
        self::assertTrue($report->tabbed());
        self::assertEquals(['one', 'goodbye'], $report->tabLabels());
    }

    public function testNoConfiguredTabLabels(): void
    {
        $generator = $this->createGenerator($this->container());
        $reports = $generator->generate(TestUtil::createCollection([[]]), $this->resolveConfig($generator, [
            ComponentGenerator::PARAM_TABBED => true,
            ComponentGenerator::PARAM_COMPONENTS => [
                [
                    'component' => 'table_aggregate',
                    'title' => 'hello',
                ],
                [
                    'component' => 'table_aggregate',
                    'title' => 'goodbye',
                ]
            ]
        ]));
        $report = $reports->first();
        self::assertTrue($report->tabbed());
        self::assertEquals(['hello', 'goodbye'], $report->tabLabels());
    }

    public function testFilterDataFrame(): void
    {
        $generator = $this->createGenerator($this->container());
        $reports = $generator->generate(TestUtil::createCollection([[
            'iterations' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
        ]]), $this->resolveConfig($generator, [
            ComponentGenerator::PARAM_FILTER => 'iteration_index < 5',
            ComponentGenerator::PARAM_COMPONENTS => [
                [
                    'component' => 'table_aggregate',
                    'title' => 'hello',
                    'partition' => ['iteration_index'],
                    'row' => [
'iteration_index' => 'partition["iteration_index"]',
                    ],
                ],
            ]
        ]));
        $report = $reports->first();
        $table = $report->objects()[0];
        assert($table instanceof Table);
        self::assertCount(5, $table->rows());
    }
}
