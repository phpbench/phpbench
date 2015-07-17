<?php

namespace PhpBench\Report\Dom\functions;

use PhpBench\Report\Calculator;

function deviation($standardValue, $value)
{
    return Calculator::deviation($standardValue, $value);
}

function avg($values)
{
    return Calculator::mean($values);
}

function sum($values)
{
    return Calculator::sum($values);
}

function min($values)
{
    return Calculator::min($values);
}

function max($values)
{
    return Calculator::max($values);
}

function median($values)
{
    return Calculator::median($values);
}
