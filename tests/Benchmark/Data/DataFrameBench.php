<?php

namespace PhpBench\Tests\Benchmark\Data;

use PhpBench\Data\DataFrame;

use function array_fill;

class DataFrameBench
{
    /** @var list<list<int>> */
    private array $series;

    /** @var list<int> */
    private array $columns;

    /** @var list<list<int>> */
    private ?array $records = null;

    public function setUpRowArrays(): void
    {
        $this->series = array_fill(0, 1000, range(0, 100));
        $this->columns = range(0, 100);
    }

    /**
     * @BeforeMethods("setUpRowArrays")
     */
    public function benchCreateFromRowArrays(): void
    {
        DataFrame::fromRowSeries($this->series, $this->columns);
    }

    public function setUpRecords(): void
    {
        $this->records = array_fill(0, 1000, range(0, 100));
    }

    /**
     * @BeforeMethods("setUpRecords")
     */
    public function benchCreateFromRecords(): void
    {
        DataFrame::fromRecords($this->records);
    }
}
