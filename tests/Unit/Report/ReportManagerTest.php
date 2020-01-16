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

use PhpBench\Console\OutputAwareInterface;
use PhpBench\Dom\Document;
use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Config;
use PhpBench\Registry\ConfigurableRegistry;
use PhpBench\Report\GeneratorInterface;
use PhpBench\Report\RendererInterface;
use PhpBench\Report\ReportManager;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Output\OutputInterface;

class ReportManagerTest extends TestCase
{
    private $reportManager;
    private $generator;
    private $suiteCollection;
    private $output;

    protected function setUp(): void
    {
        $this->rendererRegistry = $this->prophesize(ConfigurableRegistry::class);
        $this->generatorRegistry = $this->prophesize(ConfigurableRegistry::class);

        $this->reportManager = new ReportManager(
            $this->generatorRegistry->reveal(),
            $this->rendererRegistry->reveal()
        );

        $this->generator = $this->prophesize(GeneratorInterface::class);
        $this->renderer = $this->prophesize(RendererInterface::class);
        $this->outputRenderer = $this->prophesize(RendererInterface::class)
            ->willImplement(OutputAwareInterface::class);
        $this->output = $this->prophesize(OutputInterface::class);

        $this->suiteCollection = new SuiteCollection();
        $this->reportsDocument = new Document();

        $reportsEl = $this->reportsDocument->createRoot('reports');
        $reportsEl->setAttribute('name', 'test_report');
        $reportEl = $reportsEl->appendElement('report');
        $reportEl->appendElement('description');
    }

    /**
     * It should render a report.
     */
    public function testRender()
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
        $this->generator->generate($this->suiteCollection, $config)->willReturn($this->reportsDocument);

        $this->rendererRegistry->getConfig('console_output')->willReturn($outputConfig);
        $this->rendererRegistry->getService('renderer')->willReturn(
            $this->renderer->reveal()
        );
        $this->renderer->render($this->reportsDocument, $outputConfig)->shouldBeCalled();

        $this->reportManager->renderReports(
            $this->output->reveal(),
            $this->suiteCollection,
            ['test_report'],
            ['console_output']
        );
    }

    /**
     * It should throw an exception if the generator does not return a Document class.
     *
     */
    public function testGeneratorNotReturnDocument()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Report generator "service" should');
        $config = new Config('test', [
            'generator' => 'service',
            'one' => 'two',
        ]);
        $this->generatorRegistry->getConfig('test_report')->willReturn($config);
        $this->generatorRegistry->getService('service')->willReturn(
            $this->generator->reveal()
        );
        $this->generator->generate($this->suiteCollection, $config)->willReturn(null);

        $this->reportManager->renderReports(
            $this->output->reveal(),
            $this->suiteCollection,
            ['test_report'],
            ['console_output']
        );
    }

    /**
     * Output aware generators should hvae the output set on them.
     */
    public function testRenderOutputAware()
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
        $this->generator->generate($this->suiteCollection, $config)->willReturn($this->reportsDocument);

        $this->rendererRegistry->getService('renderer')->willReturn(
            $this->outputRenderer->reveal()
        );
        $this->outputRenderer->render($this->reportsDocument, $outputConfig)->shouldBeCalled();
        $this->outputRenderer->setOutput($this->output->reveal())
            ->shouldBeCalled();
        $this->rendererRegistry->getConfig('console_output')->willReturn($outputConfig);

        $this->reportManager->renderReports(
            $this->output->reveal(),
            $this->suiteCollection,
            ['test_report'],
            ['console_output']
        );
    }
}
