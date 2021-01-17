<?php

namespace PhpBench\Tests\Unit\Assertion;

use PHPUnit\Framework\TestCase;
use PhpBench\Assertion\ComparisonResult;

class ComparisonResultTest extends TestCase
{
    public function testComparisonResult(): void
    {
        $r1 = ComparisonResult::true();
        self::assertTrue($r1->isTrue());
        self::assertFalse($r1->isTolerated());

        $r1 = ComparisonResult::false();
        self::assertFalse($r1->isTrue());
        self::assertFalse($r1->isTolerated());

        $r1 = ComparisonResult::tolerated();
        self::assertFalse($r1->isTrue());
        self::assertTrue($r1->isTolerated());
    }
}
