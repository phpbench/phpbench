<?php

namespace PhpBench\Benchmark\Metadata\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD)]
class OutputTimeUnit
{
    /**
     * @var string
     */
    public $timeUnit;

    /**
     * @var int
     */
    public $precision;

    public function __construct(string $timeUnit, int $precision)
    {
        $this->timeUnit = $timeUnit;
        $this->precision = $precision;
    }
}
