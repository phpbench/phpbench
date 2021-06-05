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
     * @var string|null
     */
    public $title;

    /**
     * @var string
     */
    public $yAxesLabel;

    /**
     * @param BarChartDataSet[] $dataSets
     */
    public function __construct(array $dataSets, ?string $title, ?string $yAxesLabel)
    {
        $this->dataSets = $dataSets;
        $this->title = $title;
        $this->yAxesLabel = $yAxesLabel;
    }

    /**
     * @return number[]
     */
    public function yValues(): array
    {
        if (!$this->dataSets) {
            return [];
        }

        return array_merge(...array_map(function (BarChartDataSet $dataSet) {
            return $dataSet->ySeries;
        }, $this->dataSets));
    }

    /**
     * @return scalar[]
     */
    public function xAxes(): array
    {
        if (!$this->dataSets) {
            return [];
        }

        $xAxes = array_unique(array_merge(...array_map(function (BarChartDataSet $dataSet) {
            return $dataSet->xSeries;
        }, $this->dataSets)));
        sort($xAxes);

        return $xAxes;
    }

    public function isEmpty(): bool
    {
        foreach ($this->dataSets as $dataSet) {
            if (empty($dataSet->ySeries)) {
                return true;
            }
        }

        return false;
    }
}
