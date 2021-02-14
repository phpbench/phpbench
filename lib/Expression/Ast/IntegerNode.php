<?php

namespace PhpBench\Expression\Ast;

use PhpBench\Expression\Ast\NumberNode;

class IntegerNode implements NumberNode
{
    /**
     * @var int
     */
    private $value;

    public function __construct(int $value)
    {
        $this->value = $value;
    }

    public function value(): int
    {
        return $this->value;
    }
}
