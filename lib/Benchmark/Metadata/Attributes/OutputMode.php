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

namespace PhpBench\Benchmark\Metadata\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS|Attribute::TARGET_METHOD)]
class OutputMode
{
    /**
     * @var string
     */
    private $mode;

    public function __construct(string $mode)
    {
        $this->mode = $mode;
    }

    public function getMode()
    {
        return $this->mode;
    }
}