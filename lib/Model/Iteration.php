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
 * Represents the data required to execute a single iteration.
 */
class Iteration extends ResultCollection
{
    private $variant;
    private $index;

    /**
     * @param int $index
     * @param Variant $variant
     * @param array $results
     */
    public function __construct(
        $index,
        Variant $variant,
        array $results = []
    ) {
        $this->index = $index;
        $this->variant = $variant;
        parent::__construct($results);
    }

    /**
     * Return the Variant that this
     * iteration belongs to.
     *
     * @return Variant
     */
    public function getVariant()
    {
        return $this->variant;
    }

    /**
     * Return the index of this iteration.
     *
     * @return int
     */
    public function getIndex()
    {
        return $this->index;
    }
}
