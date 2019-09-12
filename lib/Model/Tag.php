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

namespace PhpBench\Model;

use InvalidArgumentException;

final class Tag
{
    /**
     * @var string
     */
    private $tag;

    public function __construct(string $tag)
    {
        if (!preg_match('/^[\w]+$/', $tag)) {
            throw new InvalidArgumentException(sprintf(
                'Tag mast be non-empty string of alphanumeric characters and _, got "%s"',
                $tag
            ));
        }
        $this->tag = $tag;
    }

    public function __toString()
    {
        return $this->tag;
    }
}
