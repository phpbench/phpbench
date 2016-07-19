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
     * @param string $subject
     * @param array $options
     *
     * @return string
     */
    public function format($subject, array $options);

    /**
     * Return the default options for this format class.
     *
     * @return array
     */
    public function getDefaultOptions();
}
