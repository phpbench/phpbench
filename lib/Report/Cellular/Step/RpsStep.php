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

use DTL\Cellular\Table;
use DTL\Cellular\Row;
use PhpBench\Report\Cellular\Step;

/**
 * Add revolutions per second to table rows.
 */
class RpsStep implements Step
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
