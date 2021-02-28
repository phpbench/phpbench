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

namespace PhpBench\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class Groups
{
    /**
     * @var string[]
     */
    public $groups;

    /**
     * @param string[] $groups
     */
    public function __construct(array $groups)
    {
        $this->groups = $groups;
    }
}
