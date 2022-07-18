<?php

declare(strict_types=1);

namespace PhpBench\Tests\Unit\Attributes;

use PhpBench\Attributes\Assert;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class AssertTest extends TestCase
{
    public function testHandlesMultipleAssert(): void
    {
        if ($this->shouldSkip()) {
            $this->markTestSkipped('PHP 8 only');

            return;
        }

        $reflection = new ReflectionClass(AttributeAssertUsedMultipleTimes::class);
        $attributes = $reflection->getAttributes();
        self::assertCount(2, $attributes);
        foreach ($attributes as $attribute) {
            $attribute->newInstance();
        }
    }

    private function shouldSkip(): bool
    {
        return PHP_VERSION_ID < 80000;
    }
}

#[Assert('mode(variant.mem.peak) < 2097152'), Assert('mode(variant.time.avg) < 10000000')]
class AttributeAssertUsedMultipleTimes
{
}
