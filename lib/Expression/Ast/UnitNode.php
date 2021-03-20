<?php

namespace PhpBench\Expression\Ast;

class UnitNode implements Node
{
    /**
     * @var string
     */
    private $unit;

    public function __construct(string $unit)
    {
        $this->unit = $unit;
    }

    public function unit(): string
    {
        return $this->unit;
    }
}
