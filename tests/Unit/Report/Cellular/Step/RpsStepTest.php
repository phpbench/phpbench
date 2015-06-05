<?php

namespace PhpBench\Tests\Unit\Report\Cellular\Step;

use DTL\Cellular\Workspace;
use PhpBench\Report\Cellular\Step\RpsStep;

class RpsStepTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should add a RPS (revolutions per second) column to the table
     */
    public function testRps()
    {
        $workspace = Workspace::create();
        $table = $workspace->createAndAddTable();
        $table->createAndAddRow()
            ->set('time', 1000000)
            ->set('revs', 1);
        $table->createAndAddRow()
            ->set('time', 500000)
            ->set('revs', 1);
        $table->createAndAddRow()
            ->set('time', 500000)
            ->set('revs', 2);

        $step = new RpsStep();
        $step->step($workspace);
        $this->assertCount(1, $workspace->getTables());
        $table = $workspace->getTable(0);
        $this->assertCount(3, $table->getRows());
        $rows = $table->getRows();
        $this->assertArrayHasKey('rps', $rows[0]);
        $this->assertEquals(1, $rows[0]['rps']->getValue());
        $this->assertEquals(2, $rows[1]['rps']->getValue());
        $this->assertEquals(4, $rows[2]['rps']->getValue());
    }
}
