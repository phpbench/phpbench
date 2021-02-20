<?php

namespace PhpBench\Expression\Value;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\NumberNode;

class TolerableValue implements Node
{
    /**
     * @var NumberNode
     */
    public $value;

    /**
     * @var 
     */
    public $tolerance;

    /**
     * @param mixed $value
     * @param mixed $tolerance
     */
    public function __construct(NumberNode, $tolerance)
    {
        $this->value = $value;
        $this->tolerance = $tolerance;
    }
}
