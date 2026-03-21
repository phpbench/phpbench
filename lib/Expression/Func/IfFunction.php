<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\PhpValue;

final class IfFunction
{
    public function __invoke(BooleanNode $condition, PhpValue $trueVal, PhpValue $falseVal): PhpValue
    {
        if ($condition->value() === true) {
            return $trueVal;
        }

        return $falseVal;
    }
}
