<?php

namespace PhpBench\Report\Func;

function fit_to_axis(array $axis, $rows)
{
    return (new FitToAxis())->__invoke($axis, $rows);
}
