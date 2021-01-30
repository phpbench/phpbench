<?php

namespace PhpBench\Assertion\Ast;

use PhpBench\Assertion\Ast\ExpressionNode;

interface NumberNode extends ExpressionNode
{
    public function value();
}
