<?php

namespace PhpBench\Expression\Ast;

final class UnitNode extends Node
{
    public function __construct(private readonly Node $unit)
    {
    }

    public function unit(): Node
    {
        return $this->unit;
    }
}
