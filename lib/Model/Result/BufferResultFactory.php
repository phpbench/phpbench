<?php

namespace PhpBench\Model\Result;

use PhpBench\Model\ResultFactory;
use PhpBench\Model\ResultInterface;

class BufferResultFactory implements ResultFactory
{
    /**
     * {@inheritDoc}
     */
    public function create(array $data): ResultInterface
    {
        return new BufferResult($data['buffer']);
    }
}
