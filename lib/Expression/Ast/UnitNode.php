<?php

namespace PhpBench\Expression\Ast;

class UnitNode implements Node
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
