<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Report\Generator\Tabular\Format;

use PhpBench\Tabular\Formatter\FormatInterface;
use PhpBench\Util\TimeUnit;

/**
 * Formater which converts one time unit to another,.
 */
class TimeFormat implements FormatInterface
{
    /**
     * @var TimeUnit
     */
    private $timeUnit;

    public function __construct(TimeUnit $timeUnit)
    {
        $this->timeUnit = $timeUnit;
    }

    /**
     * {@inheritdoc}
     */
    public function format($subject, array $options)
    {
        return $this->timeUnit->format(
            $subject,
            in_array('unit', $options['resolve']) ? $this->timeUnit->resolveDestUnit($options['unit']) : $options['unit'],
            in_array('mode', $options['resolve']) ? $this->timeUnit->resolveMode($options['mode']) : $options['mode']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array(
            'unit' => TimeUnit::MICROSECONDS,
            'mode' => TimeUnit::MODE_TIME,
            'resolve' => array(),
        );
    }
}
