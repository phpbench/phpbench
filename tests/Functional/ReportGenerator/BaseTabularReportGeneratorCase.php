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
     * It should allow explode_param.
     */
    public function testWithExplodeParam()
    {
        $this->executeReport($this->getResults(), array(
            'explode_param' => 'foo',
        ));
    }

    /**
     * It should allow memory
     */
    public function testWithMemory()
    {
        $this->executeReport($this->getResults(), array(
            'memory' => true,
        ));
    }

    /**
     * It should allow memory inclusive
     */
    public function testWithMemoryInclusive()
    {
        $this->executeReport($this->getResults(), array(
            'memory_inc' => true,
        ));
    }
}
