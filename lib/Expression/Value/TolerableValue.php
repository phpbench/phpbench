<?php

namespace PhpBench\Expression\Value;

class TolerableValue
{
    /**
     * @var mixed
     */
    public $value;

    /**
     * @var mixed
     */
    public $tolerance;

    /**
     * @param mixed $value
     * @param mixed $tolerance
     */
    public function __construct($value, $tolerance)
    {
        $this->value = $value;
        $this->tolerance = $tolerance;
    }
}
