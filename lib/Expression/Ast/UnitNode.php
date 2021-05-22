<?php

namespace PhpBench\Expression\Ast;

final class UnitNode extends Node
{
    /**
     * @var Node
     */
    private $unit;

    public function __construct(Node $unit)
    {
        $this->unit = $unit;
    }

    public function unit(): Node
    {
        return $this->unit;
    }
}
