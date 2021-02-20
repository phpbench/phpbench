<?php

namespace PhpBench\Expression\Ast;

use RuntimeException;
use function is_float;

final class NumberNodeFactory
{
    public static function fromNumber($number): NumberNode
    {
        if (is_float($number)) {
            return new FloatNode($number);
        }

        if (is_int($number)) {
            return new IntegerNode($number);
        }

        throw new RuntimeException(sprintf(
            'Invalid number value "%s"', gettype($number)
        ));
    }
}
