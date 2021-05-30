<?php

namespace PhpBench\Report\Model;

use PhpBench\Report\ComponentInterface;

class BarChart implements ComponentInterface
{
    /**
     * @var BarChartDataSet[]
     */
    public $dataSets;

    /**
     * @param BarChartDataSet[] $dataSets
     */
    public function __construct(array $dataSets)
    {
        $this->dataSets = $dataSets;
    }
}
