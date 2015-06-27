<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Report\Cellular\Step;

use DTL\Cellular\Workspace;
use DTL\Cellular\Table;
use DTL\Cellular\Calculator;
use PhpBench\Report\Cellular\Step;
use DTL\Cellular\Row;

class AggregateStep implements Step
{
    private $function;
    private $attributes;

    /**
     * @param array $attributes
     */
    public function __construct($function, array $attributes)
    {
        $this->function = $function;
        $this->attributes = $attributes;
    }

    public function step(Workspace $workspace)
    {
        $workspace
            ->partition(function (Table $table) {
                $key = array();
                foreach ($this->attributes as $attribute) {
                    $key[] = json_encode($table->getAttribute($attribute));
                }

                return implode('', $key);
            })
            ->aggregate(function (Workspace $workspace, $newWorkspace) {
                if (!$workspace->first()) {
                    return;
                }

                foreach ($workspace as $table) {
                    if (!isset($newWorkspace[0])) {
                        $newTable = $newWorkspace->createAndAddTable();
                        $newTable->setAttribute(
                            'description',
                            sprintf(
                                'Aggregate: %s',
                                json_encode($this->attributes)
                            )
                        );
                    } else {
                        $newTable = $newWorkspace->first();
                    }

                    $row = $newTable->createAndAddRow();
                    $row->set('iters', $table->count());
                    $row->set('params', $table->getAttribute('parameters'));
                    $row->set('classname', basename(str_replace('\\', '//', $table->getAttribute('class'))));
                    $row->set('class', $table->getAttribute('class'));
                    $row->set('subject', $table->getAttribute('subject'));
                    $row->set('description', $table->getAttribute('description'));

                    $this->applyAggregation($table, $row);
                }
            });
    }

    /**
     * @param Table $table
     * @param Row $row
     */
    protected function applyAggregation(Table $table, Row $row)
    {
        $function = $this->function;

        foreach ($table->first()->getCells(array('aggregate')) as $colName => $cell) {
            $row->set(
                $colName,
                Calculator::$function($table->getColumn($colName)),
                $cell->getGroups()
            );
        }

        $row->set(
            'variance',
            Calculator::deviation(
                Calculator::min($table->getColumn('time')),
                Calculator::max($table->getColumn('time'))
            ),
            array('.variance')
        );
    }
}
