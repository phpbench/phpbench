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
     * {@inheritdoc}
     */
    public static function fromArray(array $values)
    {
        return new self((int) $values['net']);
    }

    /**
     * @param mixed $time Time taken to execute the iteration in microseconds.
     */
    public function __construct($time)
    {
        Assertion::greaterOrEqualThan($time, 0, 'Time cannot be less than 0, got %s');
        Assertion::integer($time);

        $this->netTime = $time;
    }

    /**
     * Return the net-time for this iteration.
     *
     * @return int
     */
    public function getNet()
    {
        return $this->netTime;
    }

    /**
     * Return the time for the given number of revolutions.
     *
     * @param int $revs
     *
     * @throws \OutOfBoundsException If revs <= 0
     *
     * @return float
     */
    public function getRevTime($revs)
    {
        Assertion::greaterThan($revs, 0, 'Revolutions must be more than 0, got %s');

        return $this->netTime / $revs;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetrics()
    {
        return [
            'net' => $this->netTime,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'time';
    }
}
