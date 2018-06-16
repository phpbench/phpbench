<?php

namespace PhpBench\Runner\Stage;

use OutOfBoundsException;
use PhpBench\Runner\Stage;

class IterationLimiter implements Stage
{
    /**
     * @var int
     */
    private $limit;

    public function __construct(int $limit = 1)
    {
        if (!$limit < 1) {
            throw new OutOfBoundsException(sprintf(
                'Limit must be greater than 0, got %d',
                $limit
            ));
        }

        $this->limit = $limit;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        $data = yield;

        for ($i = 0; $i < $this->limit; $i++) {
            $data = yield $data;
        }
    }
}
