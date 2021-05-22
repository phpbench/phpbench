<?php

namespace PhpBench\Expression\Ast;

class FunctionNode extends Node
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var ArgumentListNode|null
     */
    private $arguments;

    public function __construct(string $name, ?ArgumentListNode $arguments = null)
    {
        $this->name = $name;
        $this->arguments = $arguments;
    }

    public function args(): ?ArgumentListNode
    {
        return $this->arguments;
    }

    public function name(): string
    {
        return $this->name;
    }
}
