<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\PhpValue;

final class IfFunction
{
    public function __invoke(PhpValue $condition, PhpValue $trueVal, PhpValue $falseVal): PhpValue
    {
        if ((bool)$condition->value() === true) {
            return $trueVal;
        }

        return $falseVal;
    }
}
