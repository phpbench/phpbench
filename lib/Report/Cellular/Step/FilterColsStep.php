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
use DTL\Cellular\Cell;
use DTL\Cellular\Row;
use DTL\Cellular\Table;

/**
 * This step removes all of the columns passed in the constructor from each row.
 */
class FilterColsStep implements Step
{
    /**
     * @var string[]
     */
    private $cols;

    /**
     * @param string[] $cols
     */
    public function __construct(array $cols)
    {
        $this->cols = $cols;
    }

    /**
     * {@inheritDoc}
     */
    public function step(Workspace $workspace)
    {
        if (empty($this->cols)) {
            return;
        }

        $workspace->each(function (Table $table) {
            $columnNames = $table->getColumnNames();

            $unknownColumns = array();
            foreach ($this->cols as $col) {
                if (false === in_array($col, $columnNames)) {
                    $unknownColumns[] = $col;
                }
            }

            if ($unknownColumns) {
                throw new \InvalidArgumentException(sprintf(
                    'Specified column(s) "%s" is(are) not valid. Known columns are: "%s"',
                    implode('", "', $unknownColumns), implode('", "', $columnNames)
                ));
            }

            $table->each(function (Row $row) {
                $row->filter(function (Cell $cell, $colName) {
                    if (in_array($colName, $this->cols)) {
                        return true;
                    }

                    return false;
                });
            });
        });
    }
}
