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
 * Metrics calculated relative to the iteration set of the iteration to which
 * this result belongs.
 */
class ComputedResult implements ResultInterface
{
    private $zValue;
    private $deviation;

    /**
     * {@inheritdoc}
     */
    public static function fromArray(array $values)
    {
        return new self(
            (float) $values['z_value'],
            (float) $values['deviation']
        );
    }

    /**
     * @param float $zValue
     * @param float $deviation
     */
    public function __construct($zValue, $deviation)
    {
        Assertion::numeric($zValue, 'Z-value was not numeric, got "%s"');
        Assertion::numeric($deviation, 'Deviation was not numeric, got "%s"');

        $this->zValue = $zValue;
        $this->deviation = $deviation;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetrics()
    {
        return [
            'z_value' => $this->zValue,
            'deviation' => $this->deviation,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getKey()
    {
        return 'comp';
    }

    /**
     * Return the ZValue - the number of standard
     * deviations away from the mean of the iteration
     * set to which the iteration of this result belongs.
     *
     * @return float
     */
    public function getZValue()
    {
        return $this->zValue;
    }

    /**
     * Return the percentage deviation from the mean of the
     * iteration set of the iteration to which this result
     * belongs.
     *
     * @return float
     */
    public function getDeviation()
    {
        return $this->deviation;
    }
}
