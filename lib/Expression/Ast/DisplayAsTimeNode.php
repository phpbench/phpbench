<?php

namespace PhpBench\Expression\Ast;

class DisplayAsTimeNode extends DisplayAsNode
{
    public function __construct(Node $value, UnitNode $as, ?Node $precision = null, private readonly ?Node $mode = null)
    {
        parent::__construct($value, $as, $precision);
    }

    public function mode(): ?Node
    {
        return $this->mode;
    }
}
