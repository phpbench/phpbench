<?php

namespace PhpBench\Report\Model;

class BarChartDataSet
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var scalar[]
     */
    public $xSeries;
    /**
     * @var number[]
     */
    public $ySeries;
    /**
     * @var number[]
     */
    public $errorMargins;

    /**
     * @param scalar[] $xSeries
     * @param number[] $ySeries
     * @param number[] $errorMargins
     */
    public function __construct(string $name, array $xSeries, array $ySeries, array $errorMargins)
    {
        $this->name = $name;
        $this->xSeries = $xSeries;
        $this->ySeries = $ySeries;
        $this->errorMargins = $errorMargins;
    }
}
