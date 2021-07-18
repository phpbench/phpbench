<?php

namespace PhpBench\Data\Func;

use Closure;
use PhpBench\Data\DataFrame;
use PhpBench\Data\DataFrames;

final class Partition
{
    public function __invoke(DataFrame $frame, Closure $hasher): DataFrames
    {
        $frames = [];

        foreach ($frame->rows() as $row) {
            $hash = $hasher($row);

            if (!isset($frames[$hash])) {
                $frames[$hash] = [];
            }
            $frames[$hash][] = $row->toSeries();
        }

        return new DataFrames(array_map(function (array $rows) use ($frame) {
            return new DataFrame($rows, $frame->columnNames());
        }, $frames));
    }
}
