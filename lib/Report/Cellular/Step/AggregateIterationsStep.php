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

class AggregateIterationsStep implements Step
{
    private $functions;

    public function __construct(array $functions)
    {
        $this->functions = $functions;
    }

    public function step(Workspace $workspace)
    {
        $workspace->map(function (Table $table) {
            $newTable = $table
                ->partition(function ($row) {
                    return $row['run']->getValue();
                })
                ->fork(function ($table, $newTable) {
                    if (!$table->first()) {
                        continue;
                    }

                    $row = Row::create();
                    $row->set('run', Calculator::mean($table->getColumn('run')));
                    $row->set('iters', $table->count());

                    foreach ($table->first()->getCells() as $colName => $cell) {
                        if (false === $cell->inGroup('aggregate')) {
                            continue;
                        }

                        foreach ($this->functions as $function) {
                            $row->set(
                                $function . '_' . $colName,
                                Calculator::$function($table->getColumn($colName)),
                                $cell->getGroups()
                            );
                        }
                    }
                    $newTable->addRow($row);
                });
            $newTable->setTitle($table->getTitle());
            $newTable->setDescription($table->getDescription());

            return $newTable;
        });
    }
}
