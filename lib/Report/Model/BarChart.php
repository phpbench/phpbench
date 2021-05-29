<?php

namespace PhpBench\Report\Model;

use PhpBench\Data\DataFrame;

class BarChart
{
    /**
     * @var ChartData
     */
    private $data;

    /**
     * @var ChartSeries
     */
    private $xLabels;

    public function __construct(ChartSeries $xLabels, ChartData $data)
    {
        $this->data = $data;
        $this->xLabels = $xLabels;
    }

    public function xLabels(): ChartSeries
    {
        return $this->xLabels;
    }

    public function data(): ChartData
    {
        return $this->data;
    }
}
