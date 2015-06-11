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
use PhpBench\Report\Cellular\Step\FooterStep;

class FooterStepTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should add a footer row for each function with a cell containing the
     * value of that function applied to all cells in the "footer" group.
     */
    public function testFooter()
    {
        $workspace = Workspace::create();
        $table = $workspace->createAndAddTable();
        $table->createAndAddRow()
            ->set('time', 100, array('aggregate'))
            ->set('rps', 50, array('aggregate'))
            ->set('nothing', null, array());
        $table->createAndAddRow()
            ->set('time', 50, array('aggregate'))
            ->set('rps', 500, array('aggregate'));
        $table->createAndAddRow()
            ->set('time', 0, array('aggregate'))
            ->set('rps', 5000, array('aggregate'));
        $table->align();

        $step = new FooterStep(array('mean', 'max'));
        $step->step($workspace);

        $table = $workspace->getTable(0);
        $this->assertCount(5, $table->getRows());
        $rows = $table->getRows(array('.footer'));
        $this->assertCount(2, $rows);
        $this->assertEquals(50, $rows[0]['time']->getValue());
        $this->assertEquals(100, $rows[1]['time']->getValue());
        $this->assertEquals(1850, $rows[0]['rps']->getValue());
        $this->assertEquals(5000, $rows[1]['rps']->getValue());
        $this->assertNull($rows[0]['nothing']->getValue());
    }
}
