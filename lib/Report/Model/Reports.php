<?php

namespace PhpBench\Report\Model;

use ArrayIterator;
use IteratorAggregate;
use RuntimeException;

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

    public static function fromReport(Report $report): self
    {
        return new self([$report]);
    }

    public static function fromReports(Report ...$reports): self
    {
        return new self($reports);
    }

    public static function empty(): self
    {
        return new self([]);
    }

    public function merge(Reports $reports): self
    {
        return new self(array_merge($this->reports, $reports->reports));
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->reports);
    }

    /**
     * @return Table[]
     */
    public function tables(): array
    {
        $tables = [];

        foreach ($this as $report) {
            foreach ($report->tables() as $table) {
                $tables[] = $table;
            }
        }

        return $tables;
    }

    public function first(): Report
    {
        if (empty($this->reports)) {
            throw new RuntimeException('Reports collection is empty, cannot get first');
        }

        return reset($this->reports);
    }
}
