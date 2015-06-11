<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Report\Cellular;

use DTL\Cellular\Workspace;

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
