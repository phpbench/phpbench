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
                $row->set('class', $table->getAttribute('class'));
                $row->set('subject', $table->getAttribute('subject'));
                $row->set('description', $table->getAttribute('description'));

                foreach ($table->first()->getCells() as $colName => $cell) {
                    if (false === $cell->inGroup('aggregate')) {
                        continue;
                    }

                    foreach ($this->functions as $function) {
                        $row->set(
                            $function . '_' . $colName,
                            Calculator::$function($table->getColumn($colName)),
                            $cell->getGroups()
                        );
                    }
                }
            });

        $workspace->clear();
        $workspace[] = $newWorkspace->getTable(0);
    }
}
