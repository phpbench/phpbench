<?php

namespace PhpBench\Benchmark\Metadata\Attributes;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class AfterClassMethods extends AbstractMethodsAttribute
{
}
