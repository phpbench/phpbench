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

use DTL\Cellular\Calculator;
use DTL\Cellular\Table;
use PhpBench\Report\Cellular\Step;
use DTL\Cellular\Workspace;

/**
 * Add a "footer" row for each aggregate function name given in the constructor.
 * Aggregate values will be given for cells in the "#aggregate" group.
 */
class FooterStep implements Step
{
    /**
     * @var string[]
     */
    private $functions;

    /**
     * @param string[] $functions
     */
    public function __construct(array $functions = array())
    {
        $this->functions = $functions;
    }

    public function step(Workspace $workspace)
    {
        $workspace->each(function (Table $table) {
            foreach (array_keys($this->functions) as $function) {
                $row = $table->createAndAddRow(array('.footer'));
                foreach ($table->getColumnNames(array('aggregate')) as $colName) {
                    $groups = $table->getColumn($colName)->getGroups();
                    $groups[] = 'footer';
                    $row->set(
                        $colName,
                        Calculator::$function($table->getColumn($colName)),
                        $groups
                    );
                }
                $row->set(' ', '<< ' . $function, array('.footer'));
            }

            $table->align();
        });
    }
}
