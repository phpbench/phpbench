<?php

namespace PhpBench\Assertion\Ast;

interface NumberNode extends ExpressionNode
{
    public function value();
}
