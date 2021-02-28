<?php

namespace PhpBench\Benchmark\Metadata\Attributes;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class BeforeClassMethods extends AbstractMethodsAttribute
{
}
