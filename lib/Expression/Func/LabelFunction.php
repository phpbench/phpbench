<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\LabelNode;
use PhpBench\Expression\Ast\PhpValue;

class LabelFunction
{
    public function __invoke(PhpValue $value): LabelNode
    {
        return new LabelNode($value);
    }
}
