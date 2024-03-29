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

namespace PhpBench\Tests\Unit\Report;

use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Config;
use PhpBench\Registry\ConfigurableRegistry;
use PhpBench\Report\GeneratorInterface;
use PhpBench\Report\Model\Reports;
use PhpBench\Report\RendererInterface;
use PhpBench\Report\ReportManager;
use PhpBench\Tests\TestCase;
use Prophecy\Prophecy\ObjectProphecy;

class ReportManagerTest extends TestCase
{
    private ReportManager $reportManager;

    /** @var ObjectProphecy<GeneratorInterface> */
    private ObjectProphecy $generator;

    private SuiteCollection $suiteCollection;

    /** @var ObjectProphecy<ConfigurableRegistry<RendererInterface>> */
    private ObjectProphecy $rendererRegistry;

    /** @var ObjectProphecy<ConfigurableRegistry<GeneratorInterface>> */
    private ObjectProphecy $generatorRegistry;

    /** @var ObjectProphecy<RendererInterface> */
    private ObjectProphecy $renderer;

    private Reports $reports;

    protected function setUp(): void
    {
        /** @var ObjectProphecy<ConfigurableRegistry<RendererInterface>> $rendererRegistry */
        $rendererRegistry = $this->prophesize(ConfigurableRegistry::class);
        $this->rendererRegistry = $rendererRegistry;

        /** @var ObjectProphecy<ConfigurableRegistry<GeneratorInterface>> $generatorRegistry */
        $generatorRegistry = $this->prophesize(ConfigurableRegistry::class);
        $this->generatorRegistry = $generatorRegistry;

        $this->reportManager = new ReportManager(
            $this->generatorRegistry->reveal(),
            $this->rendererRegistry->reveal()
        );

        $this->generator = $this->prophesize(GeneratorInterface::class);
        $this->renderer = $this->prophesize(RendererInterface::class);

        $this->suiteCollection = new SuiteCollection();
        $this->reports = Reports::empty();
    }

    /**
     * It should render a report.
     */
    public function testRender(): void
    {
        $config = new Config('test', [
            'generator' => 'service',
            'one' => 'two',
        ]);
        $outputConfig = new Config('test', [
            'renderer' => 'renderer',
            'three' => 'four',
        ]);
        $this->generatorRegistry->getConfig('test_report')->willReturn($config);
        $this->generatorRegistry->getService('service')->willReturn(
            $this->generator->reveal()
        );
        $this->generator->generate($this->suiteCollection, $config)->willReturn($this->reports);

        $this->rendererRegistry->getConfig('console_output')->willReturn($outputConfig);
        $this->rendererRegistry->getService('renderer')->willReturn(
            $this->renderer->reveal()
        );
        $this->renderer->render($this->reports, $outputConfig)->shouldBeCalled();

        $this->reportManager->renderReports(
            $this->suiteCollection,
            ['test_report'],
            ['console_output']
        );
    }
}
