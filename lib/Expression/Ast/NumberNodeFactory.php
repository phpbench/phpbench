<?php

namespace PhpBench\Expression\Ast;

use function is_float;
use RuntimeException;

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
