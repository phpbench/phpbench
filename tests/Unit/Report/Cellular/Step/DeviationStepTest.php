<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\Unit\Report\Cellular\Step;

use DTL\Cellular\Workspace;
use PhpBench\Report\Cellular\Step\DeviationStep;
use PhpBench\Report\Cellular\Step;

class DeviationStepTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should add a deviation from the mean as a percentage for each row for
     * a given column value.
     */
    public function testDeviation()
    {
        $step = new DeviationStep('time', array('min'));
        $rows = $this->getRows($step);

        $this->assertArrayHasKey('deviation_min', $rows[0]);
        $this->assertEquals(100, $rows[0]['deviation_min']->getValue());
        $this->assertEquals(10, $rows[1]['deviation_min']->getValue());
        $this->assertEquals(0, $rows[2]['deviation_min']->getValue());
    }

    /**
     * It should allow the specification of a function.
     */
    public function testDeviationFunction()
    {
        $step = new DeviationStep('time', array('max'));
        $rows = $this->getRows($step);

        $this->assertArrayHasKey('deviation_max', $rows[0]);
        $this->assertEquals(0, $rows[0]['deviation_max']->getValue());
        $this->assertEquals(-45, $rows[1]['deviation_max']->getValue());
        $this->assertEquals(-50, $rows[2]['deviation_max']->getValue());
    }

    private function getRows(Step $step)
    {
        $workspace = Workspace::create();
        $table = $workspace->createAndAddTable();
        $table->createAndAddRow()
            ->set('time', 20, array('deviation'))
            ->set('rps', 500, array('deviation'));
        $table->createAndAddRow()
            ->set('time', 11, array('deviation'))
            ->set('rps', 500, array('deviation'));
        $table->createAndAddRow()
            ->set('time', 10, array('deviation'))
            ->set('rps', 500, array('deviation'));

        $step->step($workspace);
        $this->assertCount(1, $workspace->getTables());
        $table = $workspace->getTable(0);
        $this->assertCount(3, $table->getRows());

        return $table->getRows();
    }
}
