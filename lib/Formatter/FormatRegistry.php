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
 * Registry of format classes.
 */
class FormatRegistry
{
    private $formats = [];

    /**
     * Register a format class.
     *
     * @param string $name
     * @param FormatInterface $format
     */
    public function register($name, FormatInterface $format)
    {
        if (isset($this->formats[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Formatter with name "%s" is already registered',
                $name
            ));
        }

        $this->formats[$name] = $format;
    }

    /**
     * Return the named format class.
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException When no formatter exists.
     *
     * @return FormatInterface
     */
    public function get($name)
    {
        if (!isset($this->formats[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'Unknown format "%s", known formats: "%s"',
                $name, implode(', ', array_keys($this->formats))
            ));
        }

        return $this->formats[$name];
    }
}
