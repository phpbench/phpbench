<?php

namespace PhpBench\Expression\Ast;

class UnitNode implements Node
{
    /**
     * @var Node
     */
    private $left;
    /**
     * @var string
     */
    private $unit;

    public function __construct(Node $left, string $unit)
    {
        $this->left = $left;
        $this->unit = $unit;
    }

    public function left(): Node
    {
        return $this->left;
    }

    public function unit(): string
    {
        return $this->unit;
    }
}
