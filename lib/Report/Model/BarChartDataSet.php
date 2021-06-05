<?php

namespace PhpBench\Report\Model;

use RuntimeException;

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
        if (count($xSeries) !== count($ySeries) || count($xSeries) !== count($errorMargins)) {
            throw new RuntimeException(sprintf(
                'X (%s) and Y (%s) and Error Margins (%s) series must have an equal number of elements',
                count($xSeries), count($ySeries), count($errorMargins)
            ));
        }
        $this->name = $name;
        $this->xSeries = $xSeries;
        $this->ySeries = $ySeries;
        $this->errorMargins = $errorMargins;
    }
}
