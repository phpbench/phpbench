<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Assertion\Ast;

use PhpBench\Assertion\Ast;

abstract class Parameter
{
    abstract public function resolveValue(Arguments $arguments): float;
}

