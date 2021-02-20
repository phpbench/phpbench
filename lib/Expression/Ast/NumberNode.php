<?php

namespace PhpBench\Expression\Ast;

interface NumberNode extends NumberValue
{
    /**
     * @return float|integer
     */
    public function value();
}
