<?php

namespace PhpBench\Expression\Ast;

interface NumberNode extends Node
{
    /**
     * @return float|integer
     */
    public function value();
}
