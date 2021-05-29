<?php

namespace PhpBench\Report\Model;

final class ChartData
{
    /**
     * @var ChartSeries[]
     */
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return ChartSeries[]
     */
    public function series(): array
    {
        return $this->data;
    }

    public function toArray()
    {
        return array_map(function (ChartSeries $chartSeries) {
            return $chartSeries->values();
        }, $this->data);
    }
}
