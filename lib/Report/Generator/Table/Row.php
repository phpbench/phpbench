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

namespace PhpBench\Report\Generator\Table;

/**
 * Repesents a *data* table row including any metadata about the row.
 */
class Row extends \ArrayObject
{
    /**
     * @var array
     */
    private $formatParams = [];

    /**
     * Return the given offset.
     * Throw an exception if the given offset does not exist.
     *
     * @throws \InvalidArgumentException
     */
    public function offsetGet($offset)
    {
        if (!$this->offsetExists($offset)) {
            throw new \InvalidArgumentException(sprintf(
                'Column "%s" does not exist, valid columns: "%s"',
                $offset, implode('", "', array_keys($this->getArrayCopy()))
            ));
        }

        return parent::offsetGet($offset);
    }

    /**
     * Merge the given array into the data of this row and
     * return a new instance.
     *
     * @param array $array
     *
     * @return Row
     */
    public function merge(array $array)
    {
        return $this->newInstance(
            array_merge(
                $this->getArrayCopy(),
                $array
            )
        );
    }

    /**
     * Return the format parameters.
     *
     * @return array
     */
    public function getFormatParams()
    {
        return $this->formatParams;
    }

    /**
     * Set the format parameters.
     *
     * @param array
     */
    public function setFormatParams($formatParams)
    {
        $this->formatParams = $formatParams;
    }

    /**
     * Return a new instance of row using the given data but
     * keeping the metadata for this row.
     *
     * @param array $array
     *
     * @return Row
     */
    public function newInstance(array $array)
    {
        $duplicate = new self($array);
        $duplicate->setFormatParams($this->getFormatParams());

        return $duplicate;
    }

    /**
     * Return the cell names for this row.
     *
     * @return string[]
     */
    public function getNames()
    {
        return array_keys($this->getArrayCopy());
    }
}
