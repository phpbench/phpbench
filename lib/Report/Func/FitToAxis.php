<?php

namespace PhpBench\Report\Func;

final class FitToAxis
{
    public function __invoke(array $axis, array $rows): array
    {
        return array_map(function (array $row) use ($axis) {
            $newRow = [];

            foreach ($axis as $key) {
                $newRow[$key] = $row[$key] ?? 0;
            }

            return array_values($newRow);
        }, $rows);
    }
}
