<?php

namespace PhpBench\Expression\Ast;

interface NumberNode extends PhpValue
{
    /**
     * @return float|integer
     */
    public function value();
}
