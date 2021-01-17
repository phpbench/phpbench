<?php

namespace PhpBench\Tests\Unit\Assertion\Ast;

use PHPUnit\Framework\TestCase;
use PhpBench\Assertion\Ast\PropertyAccess;
use PhpBench\Assertion\Exception\PropertyAccessError;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Tests\Util\VariantBuilder;

class PropertyAccessTest extends TestCase
{
    public function testExceptionOnNonExistingAccess(): void
    {
        $this->expectException(PropertyAccessError::class);
        $this->expectExceptionMessage('Array does not have');
        PropertyAccess::resolvePropertyAccess(['foo', 'bar'], []);
    }

    public function testAccessFromObject(): void
    {
        $variant = VariantBuilder::create()->setRevs(5)->build();
        self::assertEquals(5, PropertyAccess::resolvePropertyAccess(['getRevolutions'], $variant));
    }

    public function testCouldNotAccessFromObject(): void
    {
        $this->expectException(PropertyAccessError::class);
        $this->expectExceptionMessage('Could not access');
        $variant = VariantBuilder::create()->setRevs(5)->build();
        PropertyAccess::resolvePropertyAccess(['bar'], $variant);
    }
}
