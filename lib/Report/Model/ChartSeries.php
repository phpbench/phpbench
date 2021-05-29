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

    /**
     * @var array|null
     */
    private $errorMargins;

    public function __construct(array $values, ?array $errorMargins = null)
    {
        $this->values = $values;
        $this->errorMargins = $errorMargins;
    }

    /**
     * @return IteratorAggregate<int, PhpValue>
     */
    public function getIterator(): IteratorAggregate
    {
        return new IteratorAggregate($this->values);
    }

    public function values(): array
    {
        return $this->values;
    }

    public function errorMargins(): ?array
    {
        return $this->errorMargins;
    }
}
