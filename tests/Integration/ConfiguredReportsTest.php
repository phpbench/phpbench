<?php

namespace PhpBench\Tests\Integration;

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

    public function testReport(): void
    {
        $generators = $this->container()->get(ReportExtension::SERVICE_REGISTRY_GENERATOR);
        $renderers = $this->container()->get(ReportExtension::SERVICE_REGISTRY_RENDERER);

        foreach ($generators->getConfigNames() as $generator) {
            foreach (array_unique(array_merge($renderers->getServiceNames(), $renderers->getConfigNames())) as $renderer) {
                $manager = $this->container([
                    ConsoleExtension::PARAM_OUTPUT_STREAM => $this->workspace()->path('test'),
                    ConsoleExtension::PARAM_ANSI => false
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
        }
    }
}
