<?php

namespace PhpBench\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class Sleep
{
    public function __construct(public int $sleep)
    {
    }
}
