<?php

namespace PhpBench\Report\Cellular\Step;

use DTL\Cellular\Calculator;

class FooterStep implements Step
{
    private $functions;

    public function __construct(array $functions)
    {
        $this->functions = $functions;
    }

    public function step(Workspace $workspace)
    {
        $workspace->each(function (Table $table) {
            foreach ($this->functions as $function) {
                $row = $table->createAndAddRow();
                $row->set(' ', '<< ' . $function, array('footer'));
                foreach ($table->getColumnNames() as $colName) {
                    $row->set(
                        $colName, 
                        Calculator::$function($table->getColumn($colName)->getValues(array('main'))),
                        $table->getColumn($colName)->getGroups()
                    );
                }
            }
        });
    }
}
