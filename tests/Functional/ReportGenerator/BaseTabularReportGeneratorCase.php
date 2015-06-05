<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Functional\ReportGenerator;

abstract class BaseTabularReportGeneratorCase extends BaseReportGeneratorCase
{
    /**
     * It should run without any options.
     */
    public function testNoOptions()
    {
        $this->executeReport($this->getResults(), array());
    }

    /**
     * It should change the precision.
     */
    public function testWithPrecision()
    {
        $this->executeReport($this->getResults(), array(
            'precision' => 2,
        ));
    }

    /**
     * It should change aggregate iterations.
     */
    public function testWithAggregateIterations()
    {
        $this->executeReport($this->getResults(), array(
            'aggregate_iterations' => true,
        ));
    }

    /**
     * It should allow revolutions.
     */
    public function testWithRevolutions()
    {
        $this->executeReport($this->getResults(), array(
            'rps' => true,
        ));
    }

    /**
     * It should allow memory.
     */
    public function testWithMemory()
    {
        $this->executeReport($this->getResults(), array(
            'memory' => true,
        ));
    }

    /**
     * It should allow memory inclusive.
     */
    public function testWithMemoryInclusive()
    {
        $this->executeReport($this->getResults(), array(
            'memory_inc' => true,
        ));
    }

    /**
     * It should display time as a fraction of a second.
     */
    public function testWithTimeFraction()
    {
        $this->executeReport($this->getResults(), array(
            'time_format' => 'fraction',
        ));
    }

    /**
     * It should display time as the number of microseconds.
     */
    public function testWithTimeInteger()
    {
        $this->executeReport($this->getResults(), array(
            'time_format' => 'integer',
        ));
    }

    /**
     * It should display deviations.
     */
    public function testDeviation()
    {
        $this->executeReport($this->getResults(), array(
            'deviation' => true,
        ));
    }

    /**
     * It should allow groups to be specified
     */
    public function testGroups()
    {
        $this->executeReport($this->getResults(), array(
            'groups' => array('foo'),
        ));
    }
}
