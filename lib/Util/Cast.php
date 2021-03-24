<?php

namespace PhpBench\Util;

class Cast
{
    /**
     * @param mixed $value
     */
    public static function toInt($value): int
    {
        return (int)$value;
    }

    /**
     * @param mixed $value
     */
    public static function toIntOrNull($value): ?int
    {
        if (null === $value) {
            return null;
        }

        return (int)$value;
    }

    /**
     * @param mixed $value
     */
    public static function toStringOrNull($value): ?string
    {
        if (null === $value) {
            return null;
        }

        return (string)$value;
    }

    /**
     * @param mixed[] $values
     *
     * @return string[]
     */
    public static function toStrings(array $values): array
    {
        return array_map(function ($value): string {
            assert(is_string($value));

            return $value;
        }, $values);
    }
}
