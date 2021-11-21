<?php

namespace PhpBench\Executor\Parser\Ast;

class StageNode
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var StageNode[]
     */
    public $children;

    /**
     * @param StageNode[] $children
     */
    public function __construct(string $name, array $children = [])
    {
        $this->name = $name;
        $this->children = $children;
    }
}
