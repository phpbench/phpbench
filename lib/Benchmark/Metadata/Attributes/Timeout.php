<?php

namespace PhpBench\Benchmark\Metadata\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class Timeout
{
    /**
     * @var float
     */
    public $timeout;

    public function __construct(float $timeout)
    {
        $this->timeout = $timeout;
    }
}
