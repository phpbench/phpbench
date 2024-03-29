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

namespace PhpBench\Benchmark\Metadata\Annotations;

/**
 * @Annotation
 *
 * @Taget({"METHOD", "CLASS"})
 *
 * @Attributes({
 *
 *    @Attribute("value", required = true, type="mixed")
 * })
 */
class Iterations
{
    /** @var int[] */
    private readonly array $iterations;

    /**
     * @param array{value: int[]} $params
     */
    public function __construct($params)
    {
        $this->iterations = (array) $params['value'];
    }

    /**
     * @return int[]
     */
    public function getIterations(): array
    {
        return $this->iterations;
    }
}
