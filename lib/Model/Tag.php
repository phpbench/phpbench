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
    public const REGEX_PATTERN = '[\\w\.]+';

    /**
     * @var string
     */
    private $tag;

    public function __construct(string $tag)
    {
        // be restrictive with tag chars as:
        //
        // - `-` is reserved currently (e.g. my-tag-5 will show the 5th instance of my-tag)
        // - we don't know how tags will be used in storage implementations
        if (!preg_match(sprintf('/^%s$/', self::REGEX_PATTERN), $tag)) {
            throw new InvalidTagException(sprintf(
                'Tag must be non-empty string of alphanumeric characters, "." or "_". Got "%s"',
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
