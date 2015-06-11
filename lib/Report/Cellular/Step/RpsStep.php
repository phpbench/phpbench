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
use DTL\Cellular\Workspace;

/**
 * Add revolutions per second to table rows.
 */
class RpsStep implements Step
{
    /**
     * {@inheritDoc}
     */
    public function step(Workspace $workspace)
    {
        $workspace->each(function (Table $table) {
            $table->each(function (Row $row) {
                $row->set(
                    'rps',
                    $row->getCell('time')->getValue() ? (1000000 / $row->getCell('time')->getValue()) * $row['revs']->getValue() : null,
                    array('rps', 'aggregate')
                );
            });
        });
    }
}
