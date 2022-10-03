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

use PhpBench\Model\Tag;
use PhpBench\Storage\Exception\InvalidTagException;
use PhpBench\Tests\TestCase;

class TagTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     *
     * @dataProvider provideValidTag
     */
    public function testValidTag(string $tag): void
    {
        new Tag($tag);
    }

    public function provideValidTag()
    {
        yield ['foobar'];

        yield ['FooBAR'];

        yield ['42'];

        yield ['foo42'];

        yield ['foo_42'];

        yield ['php7.2'];
    }

    /**
     * @dataProvider provideInvalidTag
     */
    public function testInvalidTag(string $tag): void
    {
        $this->expectException(InvalidTagException::class);

        new Tag($tag);
    }

    public function provideInvalidTag()
    {
        yield [''];

        yield ['foo-bar'];

        yield ['foo&#!$bar'];

        yield ['best tag'];
    }
}
