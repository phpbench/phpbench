<?php

namespace PhpBench\Expression\Ast;

final class ValueWithUnitNode extends Node
{
    /**
     * @var Node
     */
    private $left;
    /**
     * @var UnitNode
     */
    private $unit;

    public function __construct(Node $left, UnitNode $unit)
    {
        $this->left = $left;
        $this->unit = $unit;
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
