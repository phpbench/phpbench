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
    public function testExceptionNonAlphaNumericUnderscore()
    {
        $this->expectException(InvalidArgumentException::class);
        new Tag('hello-world');
    }
}
