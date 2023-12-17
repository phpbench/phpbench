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
 *
 * @Attributes({
 *
 *    @Attribute("value", required = true,  type = "array"),
 * })
 */
abstract class AbstractMethodsAnnotation extends AbstractArrayAnnotation
{
    /** @var string[] */
    private readonly array $methods;

    /**
     * @param array{value: string[]} $params
     */
    public function __construct($params)
    {
        parent::__construct($params);
        $this->methods = (array) $params['value'];
    }

    /**
     * @return string[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }
}
