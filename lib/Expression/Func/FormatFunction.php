<?php

namespace PhpBench\Expression\Func;

use function error_clear_last;
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
        error_clear_last();
        /** @phpstan-ignore-next-line */
        $formatted = @sprintf($format, ...$values);

        if (!is_string($formatted)) {
            $error = error_get_last();
            $message = isset($error['message']) ? $error['message'] : 'could not format string';
            $message = str_replace('sprintf', 'format', $message);

            throw new RuntimeException($message);
        }

        return $formatted;
    }
}
