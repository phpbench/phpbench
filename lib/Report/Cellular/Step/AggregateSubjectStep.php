<?php

namespace PhpBench\Report\Cellular\Step;

use DTL\Cellular\Workspace;
use DTL\Cellular\Table;
use DTL\Cellular\Calculator;

class AggregateSubjectStep extends AggregateIterationsStep
{
    public function step(Workspace $workspace)
    {
        $newWorkspace = $workspace
            ->partition(function (Table $table) {
                return $table->getAttribute('class') . $table->getAttribute('subject');
            })
            ->fork(function (Workspace $workspace, $newWorkspace) {
                if (!$workspace->first()) {
                    continue;
                }

                if (!isset($newWorkspace[0])) {
                    $newTable = $newWorkspace->createAndAddTable();
                } else {
                    $newTable = $newWorkspace->first();
                }

                $table = $workspace->first();
                $row = $newTable->createAndAddRow();
                $row->set('iters', $table->count(), array('#iter'));
                $row->set('class', $table->getAttribute('class'), array('#class'));
                $row->set('subject', $table->getAttribute('subject'), array('#subject'));
                $row->set('description', $table->getAttribute('description'), array('#description'));
                $row->set('time', Calculator::mean($table->getColumn('time')), array('hidden'));

                $this->applyAggregation($table, $row);
            });

        $newTable = $newWorkspace->getTable(0);
        $subjectNames = $newTable->evaluate(function ($row, $names) {
            $names[] = $row['subject']->getValue();
            return $names;
        }, array());
        $newTable->setTitle('Aggregation by subject');
        $newTable->setDescription(sprintf(
            implode(', ', $subjectNames)
        ));

        $workspace->clear();
        $workspace[] = $newTable;
    }
}
