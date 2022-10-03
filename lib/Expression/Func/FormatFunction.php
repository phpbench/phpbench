<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Ast\StringNode;
use RuntimeException;

use function error_clear_last;

final class FormatFunction
{
    public function __invoke(StringNode $format, PhpValue ...$values): StringNode
    {
        error_clear_last();

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
