<?php

namespace PhpBench\Expression\Ast;

use PhpBench\Expression\Evaluator\PhpValue;

interface NumberNode extends Node, PhpValue
{
    /**
     * @return float|integer
     */
    public function value();
}
