<?php

namespace PhpBench\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class RetryThreshold
{
    public function __construct(public int|float $retryThreshold)
    {
    }
}
