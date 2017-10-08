<?php

namespace PhpBench\Assertion;

use PhpBench\Math\Distribution;

class AssertionData
{
    /**
     * @var Distribution
     */
    private $distribution;

    public function __construct(Distribution $distribution)
    {
        $this->distribution = $distribution;
    }

    public static function fromDistribution(Distribution $distribution): AssertionData
    {
        return new self($distribution);
    }

    public function getDistribution(): Distribution
    {
        return $this->distribution;
    }
}

