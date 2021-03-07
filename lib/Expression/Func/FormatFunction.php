<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Ast\StringNode;
use function error_clear_last;
use RuntimeException;

final class FormatFunction
{
    /**
     * @return string
     */
    public function __invoke(StringNode $format, PhpValue ...$values)
    {
        error_clear_last();
        /** @phpstan-ignore-next-line */
        $formatted = @sprintf($format->value(), ...array_map(function (PhpValue $value) {
            return $value->value();
        }, $values));

        if (!is_string($formatted)) {
            $error = error_get_last();
            $message = isset($error['message']) ? $error['message'] : 'could not format string';
            $message = str_replace('sprintf', 'format', $message);

            throw new RuntimeException($message);
        }

        return new StringNode($formatted);
    }
}
