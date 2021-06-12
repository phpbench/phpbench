<?php

namespace PhpBench\Tests\Integration;

use Generator;
use PhpBench\Extension\ConsoleExtension;
use PhpBench\Extension\ReportExtension;
use PhpBench\Report\ReportManager;
use PhpBench\Tests\IntegrationTestCase;
use PhpBench\Tests\Util\Approval;
use PhpBench\Tests\Util\TestUtil;

class ConfiguredReportsTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    /**
     * @dataProvider provideReport
     */
    public function testReport(string $generator, string $renderer): void
    {
        $manager = $this->container([
            ConsoleExtension::PARAM_OUTPUT_STREAM => $this->workspace()->path('test')
        ])->get(ReportManager::class);
        assert($manager instanceof ReportManager);
        $manager->renderReports(TestUtil::createCollection([
            [],
        ]), [$generator], [$renderer]);

        $approval = Approval::create(sprintf(
            '%s/%s/%s-%s',
            __DIR__,
            '/generator/',
            $generator,
            $renderer
        ), 0);
        $contents = $this->workspace()->getContents('test');
        $contents = str_replace(getcwd(), '', $contents);
        $approval->approve($contents);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideReport(): Generator
    {
        $generators = $this->container()->get(ReportExtension::SERVICE_REGISTRY_GENERATOR);
        $renderers = $this->container()->get(ReportExtension::SERVICE_REGISTRY_RENDERER);

        foreach ($generators->getConfigNames() as $generator) {
            foreach (array_unique(array_merge($renderers->getServiceNames(), $renderers->getConfigNames())) as $renderer) {
                yield [
                    $generator,
                    $renderer
                ];
            }
        }

        foreach ($generators->getServiceNames() as $generator) {
            // composite doesn't work without configuration
            if ($generator === 'composite') {
                continue;
            }

            foreach ($renderers->getServiceNames() as $renderer) {
                yield [
                    $generator,
                    $renderer
                ];
            }
        }
    }
}
