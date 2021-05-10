<?php

namespace PhpBench\Data;

use RuntimeException;

final class Series
{
    /**
     * @var array
     */
    private $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    public function toValues(): array
    {
        return $this->values;
    }

    /**
     * @return mixed
     */
    public function value(int $index)
    {
        if (!isset($this->values[$index])) {
            throw new RuntimeException(sprintf(
                'No value exists at index "%s" in series with %s values',
                $index, count($this->values)
            ));
        }

        return $this->values[$index];
    }
}
