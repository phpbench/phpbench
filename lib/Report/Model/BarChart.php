<?php

namespace PhpBench\Report\Model;

use PhpBench\Report\ComponentInterface;
use RuntimeException;

class BarChart implements ComponentInterface
{
    /**
     * @var BarChartDataSet[]
     */
    private $dataSets;

    /**
     * @var string|null
     */
    private $title;

    /**
     * @var string
     */
    private $yAxesLabel;

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
            return $dataSet->ySeries();
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
            return $dataSet->xSeries();
        }, $this->dataSets)));
        sort($xAxes);

        return $xAxes;
    }

    public function isEmpty(): bool
    {
        foreach ($this->dataSets as $dataSet) {
            if (empty($dataSet->ySeries())) {
                return true;
            }
        }

        return false;
    }

    public function yAxesLabel(): string
    {
        return $this->yAxesLabel;
    }

    public function title(): ?string
    {
        return $this->title;
    }

    /**
     * @return BarChartDataSet[]
     */
    public function dataSets(): array
    {
        return $this->dataSets;
    }

    public function dataSet(int $offset): BarChartDataSet
    {
        if (!isset($this->dataSets[$offset])) {
            throw new RuntimeException(sprintf(
                'No data set exists at offset %s/%s',
                $offset, count($this->dataSets)
            ));
        }

        return $this->dataSets[$offset];
    }
}
