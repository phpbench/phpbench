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
use PhpBench\Util\TimeUnit;

/**
 * Represents the net time taken by a single iteration (all revolutions).
 */
class TimeResult implements ResultInterface
{
    /**
     * @var int|float
     */
    private $netTime;

    /**
     * @var int
     */
    private $revs;

    /**
     * @var string
     */
    private $unit;

    public function __construct(int $netTime, int $revs = 1, string $unit = TimeUnit::MICROSECONDS)
    {
        if ($netTime < 0) {
            throw new InvalidArgumentException(sprintf('Net time cannot be less than zero, got "%s"', $netTime));
        }

        if ($revs < 1) {
            throw new InvalidArgumentException(sprintf('Revs cannot be less than zero, got "%s"', $revs));
        }


        $this->netTime = $unit === TimeUnit::MICROSECONDS ? $netTime : TimeUnit::convert($netTime, $unit, TimeUnit::MICROSECONDS, TimeUnit::MODE_TIME);
        $this->revs = $revs;
        $this->unit = $unit;
    }

    public static function fromArray(array $values): ResultInterface
    {
        return new self(
            (int) $values['net'],
            array_key_exists('revs', $values) ? $values['revs'] : 1
        );
    }

    /**
     * TODO: This is downcasting to an int
     *
     * Return the net-time for this iteration.
     */
    public function getNet(): int
    {
        return (int)$this->netTime;
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
        if ($revs <= 0) {
            throw new InvalidArgumentException(sprintf(
                'Revolutions must be more than 0, got %s',
                $revs
            ));
        };

        return $this->netTime / $revs;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetrics(): array
    {
        return [
            'net' => $this->netTime,
            'revs' => $this->revs,
            'avg' => $this->netTime / $this->revs,
            'unit' => $this->unit,
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
