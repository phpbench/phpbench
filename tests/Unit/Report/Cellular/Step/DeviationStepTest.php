<?php

namespace PhpBench\Tests\Unit\Report\Cellular\Step;

use DTL\Cellular\Workspace;
use PhpBench\Report\Cellular\Step\DeviationStep;

class DeviationStepTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should add a deviation from the mean as a percentage for each row for
     * a given column value
     */
    public function testDeviation()
    {
        $workspace = Workspace::create();
        $table = $workspace->createAndAddTable();
        $table->createAndAddRow()
            ->set('time', 100, array('deviation'))
            ->set('rps', 500, array('deviation'));
        $table->createAndAddRow()
            ->set('time', 50, array('deviation'))
            ->set('rps', 500, array('deviation'));
        $table->createAndAddRow()
            ->set('time', 0, array('deviation'))
            ->set('rps', 500, array('deviation'));

        $step = new DeviationStep('time');
        $step->step($workspace);
        $this->assertCount(1, $workspace->getTables());
        $table = $workspace->getTable(0);
        $this->assertCount(3, $table->getRows());
        $rows = $table->getRows();
        $this->assertArrayHasKey('deviation', $rows[0]);
        $this->assertEquals(100, $rows[0]['deviation']->getValue());
        $this->assertEquals(0, $rows[1]['deviation']->getValue());
        $this->assertEquals(-100, $rows[2]['deviation']->getValue());
    }
}
