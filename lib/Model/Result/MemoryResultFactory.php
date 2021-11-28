<?php

namespace PhpBench\Model\Result;

use PhpBench\Model\ResultFactory;
use PhpBench\Model\ResultInterface;
use PhpBench\Model\Result\Exception\UnkownResultType;
use PhpBench\Model\Result\TimeResult;

final class MemoryResultFactory implements ResultFactory
{
    public function create(array $data): ResultInterface
    {
        return MemoryResult::fromArray($data);
    }
}
