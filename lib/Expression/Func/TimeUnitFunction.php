<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\BooleanNode;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Expression\Ast\StringNode;
use PhpBench\Util\TimeUnit;

final class TimeUnitFunction
{
    public function __construct(private TimeUnit $timeUnit)
    {
    }

    public function __invoke(PhpValue $unit, ?BooleanNode $abbreviate = null): StringNode
    {
        $unit = (string)$unit->value();

        if ($unit === '') {
            // resolve our base unit
            $unit = $this->timeUnit->resolveSuitableUnit(null);
        }

        $abbreviated = $this->timeUnit->getDestSuffix($unit);
        $full = $this->timeUnit->normalizeUnit($unit);
        $abbreviate = $abbreviate ? $abbreviate->value() : false;

        if ($abbreviate) {
            return new StringNode($abbreviated);
        }

        return new StringNode($full);
    }
}
