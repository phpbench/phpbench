<?php

namespace PhpBench\Report\Model;

class BarChartDataSet
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var mixed[]
     */
    public $xSeries;
    /**
     * @var mixed[]
     */
    public $ySeries;
    /**
     * @var mixed[]
     */
    public $errorMargins;

    /**
     * @param mixed[] $xSeries
     * @param mixed[] $ySeries
     * @param mixed[] $errorMargins
     */
    public function __construct(string $name, array $xSeries, array $ySeries, array $errorMargins)
    {
        $this->name = $name;
        $this->xSeries = $xSeries;
        $this->ySeries = $ySeries;
        $this->errorMargins = $errorMargins;
    }
}
