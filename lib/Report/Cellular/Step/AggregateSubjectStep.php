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

class AggregateSubjectStep extends AggregateRunStep
{
    public function step(Workspace $workspace)
    {
        $workspace
            ->partition(function (Table $table) {
                return $table->getAttribute('identifier');
            })
            ->aggregate(function (Workspace $workspace, $newWorkspace) {
                if (!$workspace->first()) {
                    return;
                }
                $table = $workspace->first();

                if (!isset($newWorkspace[0])) {
                    $newTable = $newWorkspace->createAndAddTable();
                    foreach (array('class', 'groups', 'subject') as $name) {
                        $newTable->setAttribute($name, $table->getAttribute($name));
                    }
                } else {
                    $newTable = $newWorkspace->first();
                }

                $row = $newTable->createAndAddRow();
                $row->set('iters', $table->count());
                $row->set('params', $table->getAttribute('parameters'));
                $row->set('class', $table->getAttribute('class'));
                $row->set('subject', $table->getAttribute('subject'));
                $row->set('description', $table->getAttribute('description'));
                $row->set('time', Calculator::mean($table->getColumn('time')));

                $this->applyAggregation($table, $row);
            });
    }
}
