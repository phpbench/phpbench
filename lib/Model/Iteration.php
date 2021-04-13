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
     */
    public function __construct(
        int $index,
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
     */
    public function getVariant(): Variant
    {
        return $this->variant;
    }

    /**
     * Return the index of this iteration.
     */
    public function getIndex(): int
    {
        return $this->index;
    }
}
