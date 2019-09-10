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

namespace PhpBench\Tests\Unit\Model;

use InvalidArgumentException;
use PhpBench\Model\Tag;
use PHPUnit\Framework\TestCase;

class TagTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     * @testWith
     * ["foobar"]
     * ["FooBAR"]
     * ["42"]
     * ["foo42"]
     * ["foo_42"]
     */
    public function testValidTagValue(string $tag)
    {
        new Tag($tag);
    }

    /**
     * @testWith
     * [""]
     * ["foo-bar"]
     * ["foo&#!$bar"]
     * ["foo4.2"]
     */
    public function testInvalidTagValue(string $tag)
    {
        $this->expectException(InvalidArgumentException::class);

        new Tag($tag);
    }
}
