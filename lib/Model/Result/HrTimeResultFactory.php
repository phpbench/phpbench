<?php

namespace PhpBench\Model\Result;

use PhpBench\Model\ResultFactory;
use PhpBench\Model\ResultInterface;

final class HrTimeResultFactory implements ResultFactory
{
    public function create(array $data): ResultInterface
    {
        return new TimeResult(($data['net']), $data['revs'], 'nanoseconds');
    }
}
