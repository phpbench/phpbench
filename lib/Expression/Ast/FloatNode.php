<?php

namespace PhpBench\Expression\Ast;

class FloatNode extends NumberNode
{
    /**
     * @var float
     */
    private $number;

    public function __construct(float $number)
    {
        $this->number = $number;
    }

    public function value(): float
    {
        return $this->number;
    }
}
