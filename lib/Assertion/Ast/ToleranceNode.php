<?php

namespace PhpBench\Assertion\Ast;

class ToleranceNode implements Node
{
    /**
     * @var Value
     */
    private $tolerance;

    public function __construct(Value $tolerance)
    {
        $this->tolerance = $tolerance;
    }

    public function tolerance(): Value
    {
        return $this->tolerance;
    }
}
