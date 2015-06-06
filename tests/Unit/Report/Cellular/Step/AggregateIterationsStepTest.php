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
use PhpBench\Report\Cellular\Step\AggregateIterationsStep;

class AggregateIterationsStepTest extends \PHPUnit_Framework_TestCase
{
    /**
     * It should aggregate all rows in the group "aggregate"
     * It should add a column for each function for each aggregated field
     * It should retain the title and description of the original tables.
     */
    public function testAggregate()
    {
        $workspace = Workspace::create();
        $table = $workspace->createAndAddTable();
        $table->setTitle('Hello');
        $table->setDescription('World');
        $table->createAndAddRow()
            ->set('run', 0)
            ->set('revs', 100)
            ->set('a', 10, array('aggregate'))
            ->set('b', 10, array('aggregate'));

        $table->createAndAddRow()
            ->set('run', 0)
            ->set('revs', 100)
            ->set('a', 90, array('aggregate'))
            ->set('b', 4, array('aggregate'));

        $step = new AggregateIterationsStep(array('min', 'max'));
        $step->step($workspace);

        $this->assertCount(1, $workspace->getTables());
        $table = $workspace[0];
        $this->assertEquals('Hello', $table->getTitle());
        $this->assertEquals('World', $table->getDescription());
        $this->assertCount(1, $table->getRows());
        $row = $table->getRow(0);
        $this->assertCount(6, $row->getCells());
        $this->assertCount(4, $row->getCells(array('aggregate')));
        $this->assertEquals(90, $row->getCell('max_a')->getValue());
        $this->assertEquals(10, $row->getCell('min_a')->getValue());
    }
}
