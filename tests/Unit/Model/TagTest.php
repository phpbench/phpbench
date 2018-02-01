<?php

namespace PhpBench\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;
use PhpBench\Model\Tag;
use InvalidArgumentException;

class TagTest extends TestCase
{
    public function testExceptionNonAlphaNumericUnderscore()
    {
        $this->expectException(InvalidArgumentException::class);
        new Tag('hello-world');
    }
}
