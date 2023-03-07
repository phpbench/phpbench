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
 *    @Attribute("value", required = true, type="string")
 * })
 */
class Assert
{
    /**
     * @var string
     */
    private $expression;

    /**
     * @param array<string,mixed> $params
     */
    public function __construct(array $params)
    {
        $this->expression = $params['value'];
    }

    public function getExpression(): string
    {
        return $this->expression;
    }
}
