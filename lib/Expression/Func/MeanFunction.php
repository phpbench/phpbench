<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Ast\PhpValueFactory;
use PhpBench\Math\Statistics;

final class MeanFunction
{
    public function __invoke(ListNode $values): PhpValue
    {
        return PhpValueFactory::fromValue(Statistics::mean(
            $values->nonNullPhpValues()
        ));
    }
}
