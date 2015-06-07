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
use PhpBench\Report\Cellular\Step\RpsStep;
use PhpBench\Report\Cellular\Step\SortStep;

class SortStepTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Sort the results
     * In ascending order
     * In descending order
     *
     * @dataProvider provideSort
     */
    public function testSort($order)
    {
        $workspace = Workspace::create();
        $table = $workspace->createAndAddTable();
        $table->createAndAddRow()
            ->set('time', 10);
        $table->createAndAddRow()
            ->set('time', 50);
        $table->createAndAddRow()
            ->set('time', 5);

        $step = new SortStep('time', $order);
        $step->step($workspace);
        $this->assertCount(1, $workspace->getTables());
        $table = $workspace->getTable(0);
        $this->assertCount(3, $table->getRows());
        $rows = $table->getRows();
        $this->assertArrayHasKey('time', $rows[0]);

        if ($order === 'asc') {
            $this->assertEquals(5, $rows[0]['time']->getValue());
            $this->assertEquals(10, $rows[1]['time']->getValue());
            $this->assertEquals(50, $rows[2]['time']->getValue());
        } else {
            $this->assertEquals(50, $rows[0]['time']->getValue());
            $this->assertEquals(10, $rows[1]['time']->getValue());
            $this->assertEquals(5, $rows[2]['time']->getValue());
        }
    }

    public function provideSort()
    {
        return array(
            array('asc'),
            array('desc'),
        );
    }
}
