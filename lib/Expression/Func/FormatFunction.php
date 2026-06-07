<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Ast\StringNode;

use function error_clear_last;

final class FormatFunction
{
    public function __invoke(StringNode $format, PhpValue ...$values): StringNode
    {
        error_clear_last();

        $formatted = sprintf($format->value(), ...array_map(function (PhpValue $value) {
            return $value->value();
        }, $values));

        return new StringNode($formatted);
    }
}
