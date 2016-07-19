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

namespace PhpBench\Expression\Constraint;

/**
 * Represents a logical comparison (eq, lt, gte, etc.).
 */
class Comparison extends Constraint
{
    /**
     * @var string
     */
    private $comparator;

    /**
     * @var string
     */
    private $field;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @param string $comparator
     * @param string $field
     * @param mixed $value
     */
    public function __construct($comparator, $field, $value)
    {
        $this->comparator = $comparator;
        $this->field = $field;
        $this->value = $value;
    }

    /**
     * Return the comparator.
     *
     * @return string
     */
    public function getComparator()
    {
        return $this->comparator;
    }

    /**
     * Return the field to which this comparison will be applied.
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Return the value to compare.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function replaceValue($newValue)
    {
        $this->value = $newValue;
    }
}
