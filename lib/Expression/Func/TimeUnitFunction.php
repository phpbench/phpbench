<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Util\TimeUnit;

final class TimeUnitFunction
{
    public function __construct(private TimeUnit $timeUnit)
    {
    }

    public function __invoke(StringNode $unit, ?BooleanNode $abbreviate = null): StringNode
    {
        if ($unit->value() === '') {
            // resolve our base unit
            $unit = new StringNode($this->timeUnit->resolveSuitableUnit(null));
        }

        $abbreviated = $this->timeUnit->getDestSuffix($unit->value());
        $full = $this->timeUnit->normalizeUnit($unit->value());
        $abbreviate = $abbreviate ? $abbreviate->value() : false;

        if ($abbreviate) {
            return new StringNode($abbreviated);
        }

        return new StringNode($full);
    }
}
