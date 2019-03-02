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
     * @var float
     */
    private $netTime;

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $values)
    {
        return new self((float) $values['net']);
    }

    public function __construct(float $time)
    {
        Assertion::greaterOrEqualThan($time, 0, 'Time cannot be less than 0, got %s');

        $this->netTime = $time;
    }

    /**
     * Return the net-time for this iteration.
     */
    public function getNet(): float
    {
        return $this->netTime;
    }

    /**
     * Return the time for the given number of revolutions.
     *
     * @throws \OutOfBoundsException If revs <= 0
     */
    public function getRevTime(int $revs): float
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
