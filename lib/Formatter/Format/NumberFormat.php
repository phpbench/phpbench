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

class NumberFormat implements FormatInterface
{
    public function format($subject, array $options)
    {
        if ($subject === 'INF') {
            return 'INF';
        }

        if (is_infinite((float)$subject)) {
            return 'INF';
        }

        if (!is_numeric($subject)) {
            throw new \InvalidArgumentException(sprintf(
                'Non-numeric value encountered: "%s"',
                print_r($subject, true)
            ));
        }

        return number_format(
            (float)$subject,
            $options['decimal_places'],
            $options['decimal_point'],
            $options['thousands_separator']
        );
    }

    public function getDefaultOptions()
    {
        return [
            'decimal_places' => 0,
            'decimal_point' => '.',
            'thousands_separator' => ',',
        ];
    }
}
