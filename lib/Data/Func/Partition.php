<?php

namespace PhpBench\Data\Func;

use PhpBench\Data\DataFrame;
use PhpBench\Data\DataFrames;
use PhpBench\Data\Series;
use function array_diff;

final class Partition
{
    public function __invoke(DataFrame $frame, array $columns): DataFrames
    {
        $frames = [];
        $resolvedNames = array_diff($frame->columnNames(), $columns);
        foreach ($frame->rows() as $row) {
            $hash = implode('-', array_map(function (string $column) use ($row) {
                return $row->get($column);
            }, $columns));
            if (!isset($frames[$hash])) {
                $frames[$hash] = [];
            }
            $frames[$hash][] = $row->toSeries();
        }

        return new DataFrames(array_map(function (array $rows) use ($frame, $resolvedNames) {
            return new DataFrame($rows, $frame->columnNames());
        }, $frames));
    }
}
