<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\NullNode;
use PhpBench\Expression\Ast\PhpValue;

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
