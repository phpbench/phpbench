<?php

namespace PhpBench\Expression\Func;

use RuntimeException;

final class FormatFunction
{
    /**
     * @param mixed[] $values
     *
     * @return string
     */
    public function __invoke(string $format, ...$values)
    {
        return sprintf($format, ...$values);
    }
}

