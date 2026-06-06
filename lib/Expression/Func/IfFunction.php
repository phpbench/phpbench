<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\LazyExpr;
use PhpBench\Expression\LazyFunction;

final class IfFunction implements LazyFunction
{
    public function __invoke(LazyExpr $condition, LazyExpr $trueVal, LazyExpr $falseVal): PhpValue
    {
        $condition = $condition->expect(PhpValue::class);

        if ((bool)$condition->value() === true) {
            return $trueVal->expect(PhpValue::class);
        }

        return $falseVal->expect(PhpValue::class);
    }
}
