<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Report\Generator;

use PhpBench\Report\Generator\CompositeGenerator;
use Prophecy\Argument;

class CompositeGeneratorTest extends \PHPUnit_Framework_TestCase
{
    private $generator;
    private $output;
    private $manager;

    public function setUp()
    {
        $this->manager = $this->prophesize('PhpBench\Report\ReportManager');
        $this->output = $this->prophesize('Symfony\Component\Console\Output\OutputInterface');
        $this->result = $this->prophesize('PhpBench\Benchmark\SuiteDocument');
        $this->generator = new CompositeGenerator($this->manager->reveal());
    }

    /**
     * It should generate a composite report.
     */
    public function testGenerateComposite()
    {
        $config = array('reports' => array('one', 'two'));

        $this->generator->setOutput($this->output->reveal());

        // for some reason prophecy doesn't like passing the suite document here, so just do a type check
        $this->manager->generateReports($this->output->reveal(), Argument::type('PhpBench\Benchmark\SuiteDocument'), array('one', 'two'))->shouldBeCalled();
        $this->generator->generate($this->result->reveal(), $config);
    }
}
