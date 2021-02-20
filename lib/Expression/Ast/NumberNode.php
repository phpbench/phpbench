<?php

namespace PhpBench\Expression\Ast;

interface NumberNode extends Node, PhpValue
{
    /**
     * @return float|integer
     */
    public function value();
}
