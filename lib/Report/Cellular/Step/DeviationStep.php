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
use PhpBench\Report\Cellular\Step;

/**
 * Add deviation from mean for each row for a given deviation column defaulting to "time".
 */
class DeviationStep implements Step
{
    /**
     * @var string
     */
    private $deviationColumn;

    /**
     * @var string
     */
    private $functions;

    /**
     * The column that should be used to determine the deviation.
     *
     * @param string
     */
    public function __construct($deviationColumn = 'time', array $functions = array('mean' => array('time')))
    {
        $this->deviationColumn = $deviationColumn;
        $this->functions = $functions;
    }

    /**
     * {@inheritDoc}
     */
    public function step(Workspace $workspace)
    {
        $workspace->each(function (Table $table) {
            $table->each(function (Row $row) use ($table) {
                foreach (array_keys($this->functions) as $function) {
                    $deviationColumn = $table->getColumn($this->deviationColumn);
                    $meanValue = Calculator::{$function}
                    ($deviationColumn);
                    $row->set(
                        'deviation_' . $function,
                        Calculator::deviation($meanValue, $row->getCell($this->deviationColumn)),
                        array('#deviation', '.deviation')
                    );
                }
            });
        });
    }
}
