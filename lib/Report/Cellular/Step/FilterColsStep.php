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

use PhpBench\Report\Cellular\Step;
use DTL\Cellular\Workspace;

/**
 * This step removes all of the columns passed in the constructor from each row.
 */
class FilterColsStep implements Step
{
    /**
     * @var string[]
     */
    private $removeCols;

    /**
     * @param string[] $removeCols
     */
    public function __construct(array $removeCols)
    {
        $this->removeCols = $removeCols;
    }

    /**
     * {@inheritDoc}
     */
    public function step(Workspace $workspace)
    {
        foreach ($this->removeCols as $removeCol) {
            $workspace->each(function ($table) use ($removeCol) {
                $table->each(function ($row) use ($removeCol) {
                    // remove by groups (col names will have changed in the aggregate iteration step)
                    foreach ($row->getColumnNames(array('#' . $removeCol)) as $colName) {
                        $row->remove($colName);
                    }

                    foreach ($row->getColumnNames(array('hidden')) as $hidden) {
                        $row->remove($hidden);
                    }
                });
            });
        }
    }
}
