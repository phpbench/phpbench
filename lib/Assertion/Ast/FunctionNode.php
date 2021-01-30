<?php

namespace PhpBench\Assertion\Ast;

use PhpBench\Assertion\Ast\ExpressionNode;

class FunctionNode implements ExpressionNode
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed[]
     */
    private $args;

    /**
     * @param mixed[] $args
     */
    public function __construct(string $name, array $args)
    {
        $this->name = $name;
        $this->args = $args;
    }

    /**
     * @return array<string, mixed>
     */
    public function args(): array
    {
        return $this->args;
    }

    public function name(): string
    {
        return $this->name;
    }
}
