<?php

namespace PhpBench\Expression\Ast;

abstract class NumberNode extends NumberValue
{
    /**
     * @return float|integer
     */
    abstract public function value();
}
