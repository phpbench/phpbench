<?php

namespace PhpBench\Executor\Parser\Ast;

class UnitNode
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var UnitNode[]
     */
    public $children;

    /**
     * @param UnitNode[] $children
     */
    public function __construct(string $name, array $children = [])
    {
        $this->name = $name;
        $this->children = $children;
    }
}
