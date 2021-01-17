<?php

namespace PhpBench\Tests\Unit\Assertion;

use PhpBench\Assertion\AssertionResult;
use PHPUnit\Framework\TestCase;

class AssertionResultTest extends TestCase
{
    public function testAccess(): void
    {
        $r = AssertionResult::tolerated();
        self::assertTrue($r->isTolerated());
        self::assertFalse($r->isFail());

        $r = AssertionResult::ok();
        self::assertFalse($r->isTolerated());
        self::assertFalse($r->isFail());

        $r = AssertionResult::fail();
        self::assertFalse($r->isTolerated());
        self::assertTrue($r->isFail());
    }
}
