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

use PhpBench\Model\SuiteCollection;
use PhpBench\Registry\Config;
use PhpBench\Report\Generator\CompositeGenerator;
use PhpBench\Report\Model\Reports;
use PhpBench\Report\ReportManager;
use PhpBench\Tests\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

class CompositeGeneratorTest extends TestCase
{
    private $generator;
    private $manager;

    /**
     * @var ObjectProphecy<SuiteCollection>
     */
    private $collection;

    protected function setUp(): void
    {
        $this->manager = $this->prophesize(ReportManager::class);
        $this->collection = $this->prophesize(SuiteCollection::class);
        $this->generator = new CompositeGenerator($this->manager->reveal());
    }

    /**
     * It should generate a composite report.
     */
    public function testGenerateComposite(): void
    {
        $config = ['reports' => ['one', 'two']];

        // for some reason prophecy doesn't like passing the suite document here, so just do a type check
        $expected = $this->getReportsDocument();
        $this->manager->generateReports(Argument::type(SuiteCollection::class), ['one', 'two'])->willReturn($expected);
        $reports = $this->generator->generate($this->collection->reveal(), new Config('test', $config));
        self::assertSame($expected, $reports);
    }

    public function getReportsDocument(): Reports
    {
        return Reports::empty();
    }
}
