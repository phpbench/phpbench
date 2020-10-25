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

/**
 * Represents the net time taken by a single iteration (all revolutions).
 */
class TimeResult implements ResultInterface
{
    /**
     * @var int
     */
    private $netTime;

    /**
     * @param int $time Time taken to execute the iteration in microseconds.
     */
    public function __construct(int $time)
    {
        Assertion::greaterOrEqualThan($time, 0, 'Time cannot be less than 0, got %s');

        $this->netTime = $time;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $values): ResultInterface
    {
        return new self((int) $values['net']);
    }

    /**
     * Return the net-time for this iteration.
     */
    public function getNet(): int
    {
        return $this->netTime;
    }

    /**
     * Return the time for the given number of revolutions.
     *
     *
     * @throws \OutOfBoundsException If revs <= 0
     *
     * @return float
     */
    public function getRevTime(int $revs)
    {
        Assertion::greaterThan($revs, 0, 'Revolutions must be more than 0, got %s');

        return $this->netTime / $revs;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetrics(): array
    {
        return [
            'net' => $this->netTime,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getKey(): string
    {
        return 'time';
    }
}
