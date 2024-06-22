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
 *    @Attribute("value", required = true, type="array"),
 * })
 */
class ParamProviders extends AbstractArrayAnnotation
{
    /** @var string[] */
    private readonly array $providers;

    /**
     * @param array{value: string[]} $params
     */
    public function __construct($params)
    {
        parent::__construct($params);
        $this->providers = (array) $params['value'];
    }

    /**
     * @return string[]
     */
    public function getProviders(): array
    {
        return $this->providers;
    }
}
