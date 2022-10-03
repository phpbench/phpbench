<?php

namespace PhpBench\Report\Model;

use PhpBench\Report\ComponentInterface;
use RuntimeException;

use function array_replace;

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
     * @var array<scalar>|null
     */
    private $xLabels;

    /**
     * @param BarChartDataSet[] $dataSets
     * @param scalar[] $xLabels
     */
    public function __construct(
        array $dataSets,
        ?string $title,
        ?string $yAxesLabel = null,
        ?array $xLabels = null
    ) {
        $this->dataSets = $dataSets;
        $this->title = $title;
        $this->yAxesLabel = $yAxesLabel;
        $this->xLabels = $xLabels;
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

        // use all unique discreet points as the X axes values
        $xAxes = array_unique(array_merge(...array_map(function (BarChartDataSet $dataSet) {
            return $dataSet->xSeries();
        }, $this->dataSets)));

        return array_values($xAxes);
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

    public function yAxesLabelExpression(): string
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
                $offset,
                count($this->dataSets)
            ));
        }

        return $this->dataSets[$offset];
    }

    /**
     * @return number[]
     */
    public function yAxisLabelRange(int $steps): array
    {
        $max = max($this->yValues());

        if (false === $max) {
            return [];
        }
        $step = $max / $steps;

        return range(0, $max, $step);
    }

    /**
     * @return scalar[]
     */
    public function xLabels(): array
    {
        return array_replace($this->xAxes(), $this->xLabels ?: []);
    }
}
