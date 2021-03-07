<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\ListNode;
use PhpBench\Expression\Ast\Node;
use PhpBench\Expression\Ast\PhpValueFactory;
use PhpBench\Math\Statistics;

final class MeanFunction
{
    public function __invoke(ListNode $values): Node
    {
        return PhpValueFactory::fromNumber(Statistics::mean($values->phpValues()));
    }
}
