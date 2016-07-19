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

namespace PhpBench\Model;

/**
 * Represents a parameter set for an Iteration.
 * This object allows the storage of the parameter set index in addition to the parameter data.
 */
class ParameterSet extends \ArrayObject
{
    /**
     * @var int
     */
    private $index;

    public function __construct($index = 0, array $parameters = [])
    {
        $this->index = $index;
        parent::__construct($parameters);
    }

    /**
     * Return the index of this parameter set.
     *
     * @return int
     */
    public function getIndex()
    {
        return $this->index;
    }
}
