<?php

namespace PhpBench\Assertion\Ast;

class Unit
{
    /**
     * @var string
     */
    private $type;

    public function __construct(string $type)
    {
        $this->type = $type;
    }
}
