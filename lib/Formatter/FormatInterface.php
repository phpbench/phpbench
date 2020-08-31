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

namespace PhpBench\Formatter;

/**
 * Format classes accept a subject value and format/transform it into
 * a different string value.
 *
 * An example might be formatting numbers, e.g: 10000 => 10,000
 */
interface FormatInterface
{
    /**
     * Format the given subject value.
     *
     */
    public function format(string $subject, array $options): string;

    /**
     * Return the default options for this format class.
     */
    public function getDefaultOptions(): array;
}
