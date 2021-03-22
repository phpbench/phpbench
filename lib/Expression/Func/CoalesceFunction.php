<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Ast\StringNode;

final class CoalesceFunction
{
    public function __invoke(PhpValue ...$values): PhpValue
    {
    }
}
