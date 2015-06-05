<?php

namespace PhpBench\Report\Cellular\Step;

use DTL\Cellular\Table;
use DTL\Cellular\Row;

/**
 * Add deviation from mean
 */
class DeviationStep
{
    private $removeCols;

    public function __construct(array $removeCols)
    {
        $this->removeCols = $removeCols;
    }

    public function step(Workspace $workspace)
    {
        $workspace->each(function (Table $table) {
            $table->each(function (Row $row) {
                foreach ($this->removeCols as $removeCol) {
                    $row->remove($removeCol);
                }
            });
        });
    }
}


