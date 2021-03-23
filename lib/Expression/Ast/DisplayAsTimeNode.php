<?php

namespace PhpBench\Expression\Ast;

class DisplayAsTimeNode extends DisplayAsNode
{
    /**
     * @var Node|null
     */
    private $mode;

    public function __construct(Node $value, UnitNode $as, ?Node $precision = null, ?Node $mode = null)
    {
        parent::__construct($value, $as, $precision);
        $this->mode = $mode;
    }

    public function mode(): ?Node
    {
        return $this->mode;
    }
}
