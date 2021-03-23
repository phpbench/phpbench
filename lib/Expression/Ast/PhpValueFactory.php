<?php

namespace PhpBench\Expression\Ast;

use function is_float;
use RuntimeException;

final class PhpValueFactory
{
    public static function fromNumber($value): PhpValue
    {
        if (is_float($value)) {
            return new FloatNode($value);
        }

        if (is_int($value)) {
            return new IntegerNode($value);
        }

        if (is_string($value)) {
            return new StringNode($value);
        }

        if (is_null($value)) {
            return new NullNode();
        }

        if (is_bool($value)) {
            return new BooleanNode($value);
        }

        throw new RuntimeException(sprintf(
            'Cannot interpret PHP value "%s"', gettype($value)
        ));
    }
}
