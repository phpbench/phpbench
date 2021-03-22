<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\NullNode;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Ast\StringNode;

final class CoalesceFunction
{
    public function __invoke(PhpValue ...$values): PhpValue
    {
        foreach ($values as $value) {
            if ($value instanceof NullNode) {
                continue;
            }
            return $value;
        }

        return new NullNode();
    }
}
