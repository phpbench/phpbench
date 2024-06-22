<?php

namespace PhpBench\Report\Func;

/**
 * @param array<array-key, int|string> $axis
 * @param list<array<int|float>> $rows
 *
 * @return list<list<int>>
 */
function fit_to_axis(array $axis, $rows)
{
    return (new FitToAxis())->__invoke($axis, $rows);
}
