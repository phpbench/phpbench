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
use DTL\Cellular\Row;
use DTL\Cellular\Table;
use DTL\Cellular\Calculator;

/**
 * This steps aggregates cell (values) in the "aggregate" group into a single row for each "run".
 * The `run`, `iters` and `param` values are not aggregated as they are constant for each run.
 */
class AggregateRunStep implements Step
{
    /**
     * @var string[]
     */
    protected $functions;

    /**
     * @param string[] $functions
     */
    public function __construct(array $functions)
    {
        $this->functions = $functions;
    }

    /**
     * {@inheritDoc}
     */
    public function step(Workspace $workspace)
    {
        $workspace->each(function (Table $table) {
            $table
                ->partition(function (Row $row) {
                    return $row['run']->getValue();
                })
                ->aggregate(function ($table, $newTable) {
                    if (!$table->first()) {
                        continue;
                    }
                    $protoRow = $table->first();

                    $row = Row::create();
                    $row->set('run', Calculator::mean($table->getColumn('run')), $protoRow['run']->getGroups());
                    $row->set('iters', $table->count());
                    $row->set('time', Calculator::mean($table->getColumn('time')));
                    $row->setCell('params', clone $protoRow['params']);

                    $this->applyAggregation($table, $row);
                    $newTable->addRow($row);
                });
        });
    }
}
