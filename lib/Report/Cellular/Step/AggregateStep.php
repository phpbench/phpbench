<?php

namespace PhpBench\Report\Cellular\Step;

use PhpBench\Report\Cellular\Step;

class AggregateIterationsStep implements Step
{
    private $functions;

    public function __construct(array $functions)
    {
        $this->functions = $functions;
    }

    public function step(Workspace $workspace)
    {
        $workspace->map(function (Table $table) {
            return $table
                ->partition(function ($row) {
                    return $row['run']->getValue();
                })
                ->fork(function ($table, $newTable) use ($cols, &$newCols, $options, $functions) {
                    if (!$table->first()) {
                        continue;
                    }

                    $row = clone $table->first();
                    $row->set('run', Calculator::mean($table->getColumn('run')));
                    $row->set('iters', $table->count());
                    $row->set('revs', Calculator::sum($table->getColumn('revs')));

                    foreach ($row->getCells() as $colName => $cell) {
                        foreach ($this->functions as $function) {
                            $row->set($function . '_' . $colName, Calculator::$function($table->getColumn($colName)), $cell->getGroups());
                        }
                    }
                    $newTable->addRow($row);
                });
        });
    }
}
