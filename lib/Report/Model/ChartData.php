<?php

namespace PhpBench\Report\Model;

final class ChartData
{
    /**
     * @var array
     */
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function toArray()
    {
        return array_map(function (ChartSeries $chartSeries) {
            return $chartSeries->toArray();
        }, $this->data);
    }
}
