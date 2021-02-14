<?php

namespace PhpBench\Expression;

final class Precedence
{
    public const PRODUCT = 10;
    public const SUM = 9;
    public const COMPARISON = 8;
    public const COMPARISON_EQUALITY = 7;
    public const LOGICAL_AND = 6;
    public const LOGICAL_OR = 5;
}
