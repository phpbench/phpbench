<?php

namespace PhpBench\Expression\Ast;

use PhpBench\Data\DataFrame;
use function is_float;

final class PhpValueFactory
{
    /**
     * @param mixed $value
     */
    public static function fromValue($value): PhpValue
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

        if (is_array($value)) {
            return new ListNode(array_map(function ($value) {
                return self::fromValue($value);
            }, $value));
        }

        if ($value instanceof DataFrame) {
            return new DataFrameNode($value);
        }

        return new UnrepresentableValueNode($value);
    }
}
