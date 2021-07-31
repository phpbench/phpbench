<?php

namespace PhpBench\Tests\Unit\Model;

use PhpBench\Model\ParameterContainer;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class ParameterContainerTest extends TestCase
{
    public function testFromValueThatCannotBeSerialized(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot serialize');
        ParameterContainer::fromValue(function (): void {
        });
    }

    public function testFromValue(): void
    {
        $value = ParameterContainer::fromValue('hello');
        self::assertEquals(serialize('hello'), $value->getValue());
    }

    public function testTypeValuePair(): void
    {
        $value = ParameterContainer::fromSerializedValue(
            serialize('hello')
        );
        self::assertEquals(serialize('hello'), $value->getValue());
        self::assertEquals('hello', $value->toUnserializedValue());
    }
}
