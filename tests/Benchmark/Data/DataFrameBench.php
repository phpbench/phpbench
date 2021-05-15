<?php

namespace PhpBench\Tests\Benchmark\Data;

use PhpBench\Data\DataFrame;
use function array_fill;


/**
 * @BeforeMethods("setUp")
 */
class DataFrameBench
{
    /**
     * @var array
     */
    private $series;

    /**
     * @var array
     */
    private $columns;

    public function setUp(): void
    {
        $this->series = array_fill(0, 1000, range(0, 100));
        $this->columns = range(0, 100);
    }

    public function benchCreate(): void
    {
        DataFrame::fromRowArray($this->series, $this->columns);
    }
}
