<?php

namespace PhpBench\Tests\Unit\Report\Cellular\Step;

use PhpBench\Report\Cellular\Step\FilterColsStep;
use DTL\Cellular\Workspace;

class FilterColsStepTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should remove columns which belong to a group named after eac
     * of the given columns prefixed with "#"
     */
    public function testFilterCols()
    {
        $workspace = Workspace::create();
        $table = $workspace->createAndAddTable();
        $table->createAndAddRow()
            ->set('time', 100, array('#time'))
            ->set('rps', 500, array('#rps'));
        $table->createAndAddRow()
            ->set('time', 50, array('#time'))
            ->set('rps', 500, array('#rps'));

        $step = new FilterColsStep(array('time'));
        $step->step($workspace);

        $this->assertCount(1, $workspace->getTables());
        $this->assertCount(2, $workspace->getTable(0)->getRows());
        $rows = $workspace->getTable(0)->getRows();
        $this->assertArrayNotHasKey('time', $rows[0]);
        $this->assertArrayNotHasKey('time', $rows[1]);
        $this->assertArrayHasKey('rps', $rows[0]);
        $this->assertArrayHasKey('rps', $rows[1]);
    }
}
