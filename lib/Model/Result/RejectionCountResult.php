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

use InvalidArgumentException;
use PhpBench\Model\ResultInterface;

class RejectionCountResult implements ResultInterface
{
    private $rejectCount;

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $values): ResultInterface
    {
        return new self(
            (int) $values['count']
        );
    }

    public function __construct(int $rejectCount)
    {
        if ($rejectCount < 0) {
            throw new InvalidArgumentException('Rejection count must be greater or equal to 0,');
        }

        $this->rejectCount = $rejectCount;
    }

    public function getRejectCount(): int
    {
        return $this->rejectCount;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetrics(): array
    {
        return [
            'count' => $this->rejectCount,
        ];
    }

    public function getKey(): string
    {
        return 'reject';
    }
}
