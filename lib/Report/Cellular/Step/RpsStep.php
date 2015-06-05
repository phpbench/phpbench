<?php

namespace PhpBench\Report\Cellular\Step;

use DTL\Cellular\Table;
use DTL\Cellular\Row;

/**
 * Add revolutions per second to table rows
 */
class RpsStep
{
    public function step(Workspace $workspace)
    {
        $workspace->each(function (Table $table) {
            $table->each(function (Row $row) {
                $row->set('rps', $row->get('time') ? (1000000 / $stats['time']) * $stats['revs'] : null);
            });
        });
    }
}
