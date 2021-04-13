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

use PhpBench\Storage\Exception\InvalidTagException;

final class Tag
{
    public const REGEX_PATTERN = '\\w+';

    /**
     * @var string
     */
    private $tag;

    public function __construct(string $tag)
    {
        if (!preg_match(sprintf('/^%s$/', self::REGEX_PATTERN), $tag)) {
            throw new InvalidTagException(sprintf(
                'Tag mast be non-empty string of alphanumeric characters and _, got "%s"',
                $tag
            ));
        }
        $this->tag = strtolower($tag);
    }

    public function __toString(): string
    {
        return $this->tag;
    }
}
