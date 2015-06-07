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
 * Sort the results
 */
class SortStep implements Step
{
    private $column;
    private $direction;

    public function __construct($column, $direction = 'desc')
    {
        $this->column = $column;
        $this->direction = $direction;
    }

    /**
     * {@inheritDoc}
     */
    public function step(Workspace $workspace)
    {
        $workspace->each(function (Table $table) {
            $table->sort(function ($row1, $row2) {
                if ($this->direction === 'asc') {
                    return $row1[$this->column]->getValue() > $row2[$this->column]->getValue();
                }

                return $row1[$this->column]->getValue() < $row2[$this->column]->getValue();
            });
        });
    }
}

