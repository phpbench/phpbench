<?php

namespace PhpBench\Report\Model;

use RuntimeException;

class BarChartDataSet
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var scalar[]
     */
    private $xSeries;
    /**
     * @var number[]
     */
    private $ySeries;
    /**
     * @var number[]
     */
    private $errorMargins;

    /**
     * @param scalar[] $xSeries
     * @param number[] $ySeries
     * @param number[] $errorMargins
     */
    public function __construct(string $name, array $xSeries, array $ySeries, ?array $errorMargins)
    {
        if (count($xSeries) !== count($ySeries) || $errorMargins !== null && count($xSeries) !== count($errorMargins)) {
            throw new RuntimeException(sprintf(
                'X (%s) and Y (%s) and Error Margins (%s) series must have an equal number of elements',
                count($xSeries),
                count($ySeries),
                count($errorMargins ?? [])
            ));
        }
        $this->name = $name;
        $this->xSeries = $xSeries;
        $this->ySeries = $ySeries;
        $this->errorMargins = $errorMargins;
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return scalar[]
     */
    public function xSeries(): array
    {
        return $this->xSeries;
    }

    /**
     * @return number[]
     */
    public function ySeries(): array
    {
        return $this->ySeries;
    }

    /**
     * @return number[]|null
     */
    public function errorMargins(): ?array
    {
        return $this->errorMargins;
    }

    /**
     * @return number
     */
    public function yValueAt(int $offset)
    {
        if (!isset($this->ySeries[$offset])) {
            throw new RuntimeException(sprintf(
                'No Y value exists at offset %s/%s',
                $offset,
                count($this->ySeries)
            ));
        }

        return $this->ySeries[$offset];
    }
}
