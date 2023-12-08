<?php

namespace PhpBench\Data;

use Countable;
use RuntimeException;

final class Series implements Countable
{
    /**
     * @param array<int, scalar|null> $values
     */
    public function __construct(private array $values)
    {
    }

    /**
     * @return array<int, scalar|null>
     */
    public function toValues(): array
    {
        return $this->values;
    }

    /**
     * @return scalar|null
     */
    public function value(int $index)
    {
        if (!array_key_exists($index, $this->values)) {
            throw new RuntimeException(sprintf(
                'No value exists at index "%s" in series with %s values',
                $index,
                count($this->values)
            ));
        }

        return $this->values[$index];
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return count($this->values);
    }
}
