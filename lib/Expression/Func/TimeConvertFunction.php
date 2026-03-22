<?php

namespace PhpBench\Expression\Func;

use PhpBench\Expression\Ast\FloatNode;
use PhpBench\Expression\Ast\NumberValue;
use PhpBench\Expression\Ast\PhpValue;
use PhpBench\Util\TimeUnit;

final class TimeConvertFunction
{
    public function __construct(private TimeUnit $timeUnit)
    {
    }

    public function __invoke(NumberValue $value, PhpValue $from, PhpValue $to): PhpValue
    {
        $from = $from->value();
        $to = $to->value();

        if ($from === null) {
            $from = TimeUnit::BASE_UNIT;
        }

        if ($to === null) {
            $to = TimeUnit::BASE_UNIT;
        }

        $result = $this->timeUnit->convertTo($value->value(), (string)$from, (string)$to);

        return new FloatNode($result);
    }
}
