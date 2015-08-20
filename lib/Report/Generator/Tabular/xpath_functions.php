<?php

/*
 * This file is part of the PHP Bench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Report\Dom\functions;

use PhpBench\Report\Tool\Calculator;

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

function parameters_to_json($values)
{
    $array = array();
    foreach ($values as $parameterEl) {
        $array[$parameterEl->getAttribute('name')] = $parameterEl->getAttribute('value');
    }

    return json_encode($array);
}

function class_name($classFqn)
{
    $parts = explode('\\', $classFqn);
    end($parts);

    return current($parts);
}
