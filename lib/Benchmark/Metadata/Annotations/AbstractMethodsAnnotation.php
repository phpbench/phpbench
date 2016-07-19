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
 * @Taget({"METHOD", "CLASS"})
 * @Attributes({
 *    @Attribute("value", required = true,  type = "array"),
 * })
 */
abstract class AbstractMethodsAnnotation extends AbstractArrayAnnotation
{
    private $methods;

    public function __construct($params)
    {
        parent::__construct($params);
        $this->methods = (array) $params['value'];
    }

    public function getMethods()
    {
        return $this->methods;
    }
}
