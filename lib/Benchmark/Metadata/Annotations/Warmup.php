<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Benchmark\Metadata\Annotations;

/**
 * @Annotation
 * @Taget({"METHOD", "CLASS"})
 * @Attributes({
 *    @Attribute("value", required = true, type="integer")
 * })
 */
class Warmup
{
    private $revs;

    public function __construct($revs)
    {
        $this->revs = (int) $revs['value'];
    }

    public function getRevs()
    {
        return $this->revs;
    }
}
