<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\DisplayAsTimeNode;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Ast\UnitNode;

final class DisplayAsTimeFunction
{
    public function __invoke(PhpValue $value, StringNode $as, ?PhpValue $precision = null, ?PhpValue $throughput = null): PhpValue
    {
        return new DisplayAsTimeNode($value, new UnitNode($as), $precision, $throughput);
    }
}
