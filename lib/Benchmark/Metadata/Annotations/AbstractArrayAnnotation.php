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
 * @Attributes({
 *
 *    @Attribute("extend", required = true, type="boolean"),
 * })
 */
abstract class AbstractArrayAnnotation
{
    private bool $extend = false;

    /**
     * @param array{extend?: bool} $params
     */
    public function __construct($params)
    {
        if (isset($params['extend'])) {
            $this->extend = $params['extend'];
        }
    }

    /**
     * @return bool
     */
    public function getExtend()
    {
        return $this->extend;
    }
}
