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

class FilterColsStep implements Step
{
    private $removeCols;

    public function __construct(array $removeCols)
    {
        $this->removeCols = $removeCols;
    }

    public function step(Workspace $workspace)
    {
        foreach ($this->removeCols as $removeCol) {
            $workspace->each(function ($table) use ($removeCol) {
                $table->each(function ($row) use ($removeCol) {
                    // remove by groups (col names will have changed in the aggregate iteration step)
                    foreach (array_keys($row->getCells(array('#' . $removeCol))) as $colName) {
                        $row->remove($colName);
                    }
                });
            });
        }
    }
}
