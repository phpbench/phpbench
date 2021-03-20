<?php

namespace PhpBench\Util;

class UnitValue
{
    /**
     * @var float
     */
    private $value;
    /**
     * @var string
     */
    private $unit;

    public function __construct(float $value, string $unit)
    {
        $this->value = $value;
        $this->unit = $unit;
    }

    public function value(): float
    {
        return $this->value;
    }
}
