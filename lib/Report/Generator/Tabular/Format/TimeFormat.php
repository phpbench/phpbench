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
     * {@inheritdoc}
     */
    public function format($subject, array $options)
    {
        return TimeUnit::convert($subject, $options['from'], $options['to']);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array(
            'from' => 'microseconds',
            'to' => 'microseconds',
        );
    }
}
