<?php

namespace PhpBench\Report\Model;

use ArrayIterator;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<int,Report>
 */
final class Reports implements IteratorAggregate
{
    /**
     * @var Report[]
     */
    private $reports;

    /**
     * @param Report[] $reports
     */
    private function __construct(array $reports)
    {
        $this->reports = $reports;
    }

    public static function fromOne(Report $report): self
    {
        return new self([$report]);
    }

    public static function fromMany(Report ...$reports): self
    {
        return new self($reports);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->reports);
    }
}
