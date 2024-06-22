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

use PhpBench\Model\ResultInterface;

/**
 * Represents the memory reported at the end of the benchmark script.
 */
class MemoryResult implements ResultInterface
{
    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $values): ResultInterface
    {
        return new self(
            (int) $values['peak'],
            (int) $values['real'],
            (int) $values['final']
        );
    }

    /**
     */
    public function __construct(private readonly int $peak, private readonly int $real, private readonly int $final)
    {
    }

    /**
     * Return peak memory usage as gathered by
     * `memory_get_peak_usage`.
     *
     * @see http://php.net/manual/en/function.memory-get-peak-usage.php
     */
    public function getPeak(): int
    {
        return $this->peak;
    }

    /**
     * Return real memory usage at the end of the script
     * as gathered by `memory_get_usage(true)`.
     *
     * @see http://php.net/manual/en/function.memory-get-usage.php
     */
    public function getReal(): int
    {
        return $this->real;
    }

    /**
     * Get memory usage at the end of the script.
     *
     * @see http://php.net/manual/en/function.memory-get-usage.php
     */
    public function getFinal(): int
    {
        return $this->final;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetrics(): array
    {
        return [
            'peak' => $this->peak,
            'real' => $this->real,
            'final' => $this->final,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getKey(): string
    {
        return 'mem';
    }
}
