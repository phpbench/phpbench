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
     * @param BarChartDataSet[] $dataSets
     */
    public function __construct(array $dataSets, ?string $title)
    {
        $this->dataSets = $dataSets;
        $this->title = $title;
    }
}
