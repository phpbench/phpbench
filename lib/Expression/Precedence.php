<?php

namespace PhpBench\Expression;

final class Precedence
{
    public const ACCESS = 120;
    public const AS = 110;
    public const PRODUCT = 100;
    public const SUM = 90;
    public const TOLERANCE = 90;
    public const COMPARISON = 80;
    public const COMPARISON_EQUALITY = 70;
    public const LOGICAL_AND = 60;
    public const LOGICAL_OR = 50;
    public const CONCAT = 50;
}
