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

use PhpBench\Dom\Document;
use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Config;
use PhpBench\Report\Generator\CompositeGenerator;
use PhpBench\Report\ReportManager;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class CompositeGeneratorTest extends TestCase
{
    private $generator;
    private $manager;

    protected function setUp(): void
    {
        $this->manager = $this->prophesize(ReportManager::class);
        $this->collection = $this->prophesize(SuiteCollection::class);
        $this->generator = new CompositeGenerator($this->manager->reveal());
    }

    /**
     * It should generate a composite report.
     */
    public function testGenerateComposite()
    {
        $config = ['reports' => ['one', 'two']];

        // for some reason prophecy doesn't like passing the suite document here, so just do a type check
        $this->manager->generateReports(Argument::type(SuiteCollection::class), ['one', 'two'])->willReturn([
            $this->getReportsDocument(),
            $this->getReportsDocument(),
        ]);
        $compositeDom = $this->generator->generate($this->collection->reveal(), new Config('test', $config));

        $this->assertEquals(4, $compositeDom->xpath()->evaluate('count(//report)'));
    }

    public function getReportsDocument()
    {
        $reportsDocument = new Document();
        $reportsEl = $reportsDocument->createRoot('reports');
        $reportsEl->appendElement('report');
        $reportsEl->appendElement('report');

        return $reportsDocument;
    }
}
