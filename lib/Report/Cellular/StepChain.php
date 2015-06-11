<?php

namespace PhpBench\Report\Cellular;

use PhpBench\Report\Cellular\ColumnConfigurator;
use DTL\Cellular\Workspace;
use PhpBench\Report\Cellular\ColumnSpecification;

class StepChain implements ColumnConfigurator
{
    private $steps;

    public function add(Step $step)
    {
        $this->steps[] = $step;
    }

    public function configureColumns(ColumnSpecification $spec)
    {
        foreach ($this->steps as $step) {
            if ($step instanceof ColumnConfigurator) {
                $step->configureColumns($spec);
            }
        }
    }

    public function run(Workspace $workspace)
    {
        foreach ($this->steps as $step) {
            $step->step($workspace);
        }
    }
}
