<?php

namespace PhpBench\Benchmark\Metadata\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD)]
final class Skip
{
}
