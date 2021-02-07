<?php

namespace PhpBench\Assertion\Ast;

interface UnitNode extends ExpressionNode
{
    public function unit(): string;
}
