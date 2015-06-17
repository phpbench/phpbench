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
use PhpBench\Report\Cellular\Step;
use DTL\Cellular\Workspace;

/**
 * Sort the results.
 */
class SortStep implements Step
{
    private $sorting = array();

    /**
     * Constructor should be passed array of sortings, e.g.
     *
     * ````
     * array('col1' => 'asc', 'col2' => 'desc')
     * ````
     *
     * @param array $sorting
     */
    public function __construct(array $sorting = array())
    {
        $this->sorting = $sorting;
    }

    /**
     * {@inheritDoc}
     */
    public function step(Workspace $workspace)
    {
        $workspace->each(function (Table $table) {
            foreach (array_reverse($this->sorting) as $column => $direction) {
                if (is_numeric($column)) {
                    $column = $direction;
                    $direction = 'asc';
                }

                $table->sort(function ($row1, $row2) use ($column, $direction) {
                    $row1Value = $row1[$column]->getValue();
                    $row2Value = $row2[$column]->getValue();

                    if ($row1Value == $row2Value) {
                        return 0;
                    }

                    $greaterThan = $row1Value > $row2Value;

                    if (strtolower($direction) === 'asc') {
                        return $greaterThan ? 1 : -1;
                    }

                    return $greaterThan ? -1 : 1;
                });
            }
        });
    }
}
