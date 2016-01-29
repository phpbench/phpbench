<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Report;

use PhpBench\Dom\Document;
use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Config;
use PhpBench\Report\ReportManager;

class ReportManagerTest extends \PHPUnit_Framework_TestCase
{
    private $reportManager;
    private $generator;
    private $suiteCollection;
    private $output;

    public function setUp()
    {
        $this->rendererRegistry = $this->prophesize('PhpBench\Registry\Registry');
        $this->generatorRegistry = $this->prophesize('PhpBench\Registry\Registry');

        $this->reportManager = new ReportManager(
            $this->generatorRegistry->reveal(),
            $this->rendererRegistry->reveal()
        );

        $this->generator = $this->prophesize('PhpBench\Report\GeneratorInterface');
        $this->renderer = $this->prophesize('PhpBench\Report\RendererInterface');
        $this->outputRenderer = $this->prophesize('PhpBench\Report\RendererInterface')
            ->willImplement('PhpBench\Console\OutputAwareInterface');
        $this->output = $this->prophesize('Symfony\Component\Console\Output\OutputInterface');

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
        $config = new Config('test', array(
            'generator' => 'service',
            'one' => 'two',
        ));
        $outputConfig = new Config('test', array(
            'renderer' => 'renderer',
            'three' => 'four',
        ));
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
            array('test_report'),
            array('console_output')
        );
    }

    /**
     * It should throw an exception if the generator does not return a Document class.
     *
     * @expectedException RuntimeException
     * @expectedExceptionMessage Report generator "service" should
     */
    public function testGeneratorNotReturnDocument()
    {
        $config = new Config('test', array(
            'generator' => 'service',
            'one' => 'two',
        ));
        $this->generatorRegistry->getConfig('test_report')->willReturn($config);
        $this->generatorRegistry->getService('service')->willReturn(
            $this->generator->reveal()
        );
        $this->generator->generate($this->suiteCollection, $config)->willReturn(null);

        $this->reportManager->renderReports(
            $this->output->reveal(),
            $this->suiteCollection,
            array('test_report'),
            array('console_output')
        );
    }

    /**
     * Output aware generators should hvae the output set on them.
     */
    public function testRenderOutputAware()
    {
        $config = new Config('test', array(
            'generator' => 'service',
            'one' => 'two',
        ));
        $outputConfig = new Config('test', array(
            'renderer' => 'renderer',
            'three' => 'four',
        ));
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
            array('test_report'),
            array('console_output')
        );
    }
}
