<?php

namespace PhpBench\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Warmup
{
    /**
     * @var int[]
     */
    public $revs;

    /**
     * @param int|int[] $revs
     */
    public function __construct(int | array $revs)
    {
        $this->revs = (array) $revs;
    }
}
