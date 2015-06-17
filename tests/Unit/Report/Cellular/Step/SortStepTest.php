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
use PhpBench\Report\Cellular\Step\SortStep;

class SortStepTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Sort the results according to the given sorting array.
     */
    public function testSort()
    {
        $workspace = Workspace::create();
        $table = $workspace->createAndAddTable();
        $table->createAndAddRow()
            ->set('time', 10)
            ->set('memory', 20);
        $table->createAndAddRow()
            ->set('time', 50)
            ->set('memory', 20);
        $table->createAndAddRow()
            ->set('time', 5)
            ->set('memory', 10);
        $table->createAndAddRow()
            ->set('time', 10)
            ->set('memory', 10);

        $step = new SortStep(array('memory' => 'asc', 'time' => 'desc'));
        $step->step($workspace);
        $this->assertCount(1, $workspace->getTables());
        $table = $workspace->getTable(0);
        $this->assertCount(4, $table->getRows());
        $rows = $table->getRows();
        $this->assertArrayHasKey('time', $rows[0]);
        $this->assertArrayHasKey('memory', $rows[0]);

        $this->assertEquals(10, $rows[0]['memory']->getValue());
        $this->assertEquals(10, $rows[0]['time']->getValue());
        $this->assertEquals(10, $rows[1]['memory']->getValue());
        $this->assertEquals(5, $rows[1]['time']->getValue());
        $this->assertEquals(20, $rows[2]['memory']->getValue());
        $this->assertEquals(50, $rows[2]['time']->getValue());
        $this->assertEquals(20, $rows[3]['memory']->getValue());
        $this->assertEquals(10, $rows[3]['time']->getValue());
    }

    /**
     * It should accept columns only.
     */
    public function testSortColumnsOnly()
    {
        $workspace = Workspace::create();
        $table = $workspace->createAndAddTable();
        $table->createAndAddRow()
            ->set('time', 10)
            ->set('memory', 20);
        $table->createAndAddRow()
            ->set('time', 50)
            ->set('memory', 20);
        $table->createAndAddRow()
            ->set('time', 5)
            ->set('memory', 10);
        $table->createAndAddRow()
            ->set('time', 10)
            ->set('memory', 10);

        $step = new SortStep(array('memory', 'time'));
        $step->step($workspace);
        $table = $workspace->getTable(0);

        $expected = array(
            array('time' => 5, 'memory' => 10),
            array('time' => 10, 'memory' => 10),
            array('time' => 10, 'memory' => 20),
            array('time' => 50, 'memory' => 20),
        );

        $this->assertEquals($expected, $table->toArray());
    }
}
