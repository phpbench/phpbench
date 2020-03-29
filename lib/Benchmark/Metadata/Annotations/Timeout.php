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
 * @Taget({"METHOD", "CLASS"})
 * @Attributes({
 *    @Attribute("value", required = true, type="float")
 * })
 */
class Timeout
{
    private $timeout;

    public function __construct($params)
    {
        $this->timeout = (float) $params['value'];
    }

    public function getTimeout()
    {
        return $this->timeout;
    }
}
