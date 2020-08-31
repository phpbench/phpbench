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

class PrintfFormat implements FormatInterface
{
    public function format(string $subject, array $options): string
    {
        return sprintf($options['format'], $subject);
    }

    public function getDefaultOptions(): array
    {
        return [
            'format' => '%s',
        ];
    }
}
