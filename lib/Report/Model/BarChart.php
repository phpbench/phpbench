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
        $this->yLabelExression = $yLabelExression;
    }

    /**
     * @return number[]
     */
    public function yValues(): array
    {
        return array_merge(...array_map(function (BarChartDataSet $dataSet) {
            return $dataSet->ySeries;
        }, $this->dataSets));
    }

    /**
     * @return scalar[]
     */
    public function xValues(): array
    {
        return array_unique(array_merge(...array_map(function (BarChartDataSet $dataSet) {
            return $dataSet->xSeries;
        }, $this->dataSets)));

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
