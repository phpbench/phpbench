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

class BalanceFormat implements FormatInterface
{
    public function format($subject, array $options)
    {
        if ($subject < 0) {
            // @phpstan-ignore-next-line switch back to positive so we can use our own prefix
            $subject = $subject * -1;

            return sprintf($options['negative_format'], $subject);
        }

        if ($subject > 0) {
            return sprintf($options['positive_format'], $subject);
        }

        return sprintf($options['zero_format'], $subject);
    }

    public function getDefaultOptions()
    {
        return [
            'zero_format' => '+%s',
            'negative_format' => '-%s',
            'positive_format' => '+%s',
        ];
    }
}
