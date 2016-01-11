<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Report\Generator;

use PhpBench\Dom\Document;
use PhpBench\Registry\Config;
use PhpBench\Report\Generator\CompositeGenerator;
use Prophecy\Argument;

class CompositeGeneratorTest extends \PHPUnit_Framework_TestCase
{
    private $generator;
    private $manager;

    public function setUp()
    {
        $this->manager = $this->prophesize('PhpBench\Report\ReportManager');
        $this->result = $this->prophesize('PhpBench\Benchmark\SuiteDocument');
        $this->generator = new CompositeGenerator($this->manager->reveal());
    }

    /**
     * It should generate a composite report.
     */
    public function testGenerateComposite()
    {
        $config = array('reports' => array('one', 'two'));

        // for some reason prophecy doesn't like passing the suite document here, so just do a type check
        $this->manager->generateReports(Argument::type('PhpBench\Benchmark\SuiteDocument'), array('one', 'two'))->willReturn(array(
            $this->getReportsDocument(),
            $this->getReportsDocument(),
        ));
        $compositeDom = $this->generator->generate($this->result->reveal(), new Config('test', $config));

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
