<?php

namespace PhpBench\Expression\Value;

class ToleratedValue implements Node
{
    /**
     * @var int|float
     */
    public $tolerated;

    /**
     * @param int|float $value
     */
    public function __construct($value)
    {
        $this->tolerated = $value;
    }
}
