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
 * @Taget({"METHOD"})
 * @Attributes({
 *    @Attribute("label", type="string")
 * })
 */
class Subject
{
    private $label;

    public function __construct($data)
    {
        $this->label = isset($data['label']) ? $data['label'] : null;
    }

    public function getLabel()
    {
        return $this->label;
    }
}
