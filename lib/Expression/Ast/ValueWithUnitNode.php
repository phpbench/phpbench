<?php

namespace PhpBench\Expression\Ast;

final class ValueWithUnitNode extends Node
{
    public function __construct(private readonly Node $left, private readonly UnitNode $unit)
    {
    }

    public function left(): Node
    {
        return $this->left;
    }

    public function unit(): UnitNode
    {
        return $this->unit;
    }
}
