<?php

namespace PhpBench\Report\Model;

use IteratorAggregate;
use PhpBench\Expression\Ast\PhpValue;

final class ChartSeries implements IteratorAggregate
{
    /**
     * @var array
     */
    private $values;

    public function __construct(...$values)
    {
        $this->values = $values;
    }

    /**
     * @return IteratorAggregate<int, PhpValue>
     */
    public function getIterator(): IteratorAggregate
    {
        return new IteratorAggregate($this->values);
    }

    public function toArray(): array
    {
        return $this->values;
    }
}
