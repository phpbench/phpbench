<?php

namespace PhpBench\Expression\Func;

final class FormatFunction
{
    /**
     * @param mixed[] $values
     *
     * @return string
     */
    public function __invoke(string $format, ...$values)
    {
        /** @phpstan-ignore-next-line */
        return sprintf($format, ...$values);
    }
}
