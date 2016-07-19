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

namespace PhpBench\Model\Result;

use Assert\Assertion;
use PhpBench\Model\ResultInterface;

class RejectionCountResult implements ResultInterface
{
    private $rejectCount;

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $values)
    {
        return new self(
            (int) $values['count']
        );
    }

    public function __construct($rejectCount)
    {
        Assertion::integer($rejectCount, 'Rejection count must be an integer');
        Assertion::greaterOrEqualThan($rejectCount, 0, 'Rejection count must be greater or equal to 0');
        $this->rejectCount = $rejectCount;
    }

    public function getRejectCount()
    {
        return $this->rejectCount;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetrics()
    {
        return [
            'count' => $this->rejectCount,
        ];
    }

    public function getKey()
    {
        return 'reject';
    }
}
