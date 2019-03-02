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

namespace PhpBench\Formatter\Format;

use PhpBench\Formatter\FormatInterface;
use PhpBench\Util\TimeUnit;

/**
 * Formater which converts one time unit to another,.
 */
class TimeUnitFormat implements FormatInterface
{
    /**
     * @var TimeUnit
     */
    private $timeUnit;

    public function __construct(TimeUnit $timeUnit = null)
    {
        $this->timeUnit = $timeUnit ?: new TimeUnit();
    }

    /**
     * {@inheritdoc}
     */
    public function format($subject, array $options)
    {
        return $this->timeUnit->format(
            (float) $subject,
            in_array('unit', $options['resolve']) ? $this->timeUnit->resolveDestUnit($options['unit']) : $options['unit'],
            in_array('mode', $options['resolve']) ? $this->timeUnit->resolveMode($options['mode']) : $options['mode'],
            $this->timeUnit->resolvePrecision($options['precision'])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return [
            'unit' => TimeUnit::MICROSECONDS,
            'mode' => TimeUnit::MODE_TIME,
            'precision' => 3,
            'resolve' => [],
        ];
    }
}
