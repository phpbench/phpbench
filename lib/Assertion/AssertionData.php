<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

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

    public static function fromDistribution(Distribution $distribution): self
    {
        return new self($distribution);
    }

    public function getDistribution(): Distribution
    {
        return $this->distribution;
    }
}
