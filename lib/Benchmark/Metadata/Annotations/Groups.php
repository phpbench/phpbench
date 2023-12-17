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
class Groups extends AbstractArrayAnnotation
{
    /** @var string[] */
    private readonly array $groups;

    /**
     * @param array{value: string[]} $params
     */
    public function __construct($params)
    {
        parent::__construct($params);
        $this->groups = (array) $params['value'];
    }

    /**
     * @return string[]
     */
    public function getGroups(): array
    {
        return $this->groups;
    }
}
