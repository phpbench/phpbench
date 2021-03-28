<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\DisplayAsTimeNode;
use PhpBench\Expression\Ast\NullNode;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Expression\Ast\UnitNode;
use PhpBench\Expression\Exception\EvaluationError;

final class DisplayAsTimeFunction
{
    public function __invoke(PhpValue $value, PhpValue $as, ?PhpValue $precision = null, ?PhpValue $throughput = null): PhpValue
    {
        if ($as instanceof NullNode) {
            return new DisplayAsTimeNode($value, new UnitNode(new StringNode('microseconds')), $precision, $throughput);
        }

        if (!$as instanceof StringNode) {
            throw new EvaluationError($value, sprintf('Unit must be a string, got "%s"', get_class($as)));
        }

        return new DisplayAsTimeNode($value, new UnitNode($as), $precision, $throughput);
    }
}
