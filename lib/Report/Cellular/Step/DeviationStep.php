<?php

namespace PhpBench\Report\Cellular\Step;

use DTL\Cellular\Table;
use DTL\Cellular\Row;

/**
 * Add deviation from mean
 */
class DeviationStep
{
    public function step(Workspace $workspace)
    {
        $workspace->each(function (Table $table) {
            $table->each(function (Row $row) {
                $meanTime = Calculator::mean($table->getColumn('time'));
                $row->set('deviation', Calculator::deviation($meanTime, $row->getCell('time')), array('deviation', 'main'));
            });
        });
    }
}

