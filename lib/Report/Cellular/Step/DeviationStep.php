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
use DTL\Cellular\Workspace;
use DTL\Cellular\Calculator;

/**
 * Add deviation from mean.
 */
class DeviationStep
{
    /**
     * @var string
     */
    private $deviationColumn;

    /**
     * The column that should be used to determine the deviation.
     *
     * @param string
     */
    public function __construct($deviationColumn = 'time')
    {
        $this->deviationColumn = $deviationColumn;
    }

    /**
     * {@inheritDoc}
     */
    public function step(Workspace $workspace)
    {
        $workspace->each(function (Table $table) {
            $table->each(function (Row $row) use ($table) {
                $meanTime = Calculator::mean($table->getColumn($this->deviationColumn));
                $row->set(
                    'deviation',
                    Calculator::deviation($meanTime, $row->getCell($this->deviationColumn)),
                    array('#deviation', '.deviation')
                );
            });
        });
    }
}
