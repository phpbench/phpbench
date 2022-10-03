<?php

namespace PhpBench\Tests\Unit\Model;

use PhpBench\Model\Exception\InvalidParameterSets;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\ParameterSets;
use PHPUnit\Framework\TestCase;

use function iterator_to_array;

class ParameterSetsTest extends TestCase
{
    public function testInvalidParametersWrapped(): void
    {
        $this->expectException(InvalidParameterSets::class);
        $this->expectExceptionMessage('Each parameter set must be an array, got "string"');
        /** @phpstan-ignore-next-line */
        ParameterSets::fromSerializedParameterSets(['asd' => 'bar']);
    }

    public function testInvalidParametersUnserialized(): void
    {
        $this->expectException(InvalidParameterSets::class);
        $this->expectExceptionMessage('Each parameter set must be an array, got "string"');
        /** @phpstan-ignore-next-line */
        ParameterSets::fromUnserializedParameterSets(['asd' => 'bar']);
    }

    public function testIteratesWithUnsafeSetNamesAsKeys(): void
    {
        $sets = iterator_to_array(ParameterSets::fromSerializedParameterSets([
            'one' => [
                'one' => serialize('hello')
            ],
            'two' => [
                'one' => serialize('hello'),
            ],
        ]), true);
        self::assertEquals(['one', 'two'], array_keys($sets));
        $first = reset($sets);
        self::assertInstanceOf(ParameterSet::class, $first);
        assert($first instanceof ParameterSet);
        self::assertEquals('one', $first->getName());
        self::assertEquals([
            'one' => 'hello',
        ], $first->toUnserializedParameters());
    }

    public function testIteratesWithSetNamesAsKeys(): void
    {
        $sets = iterator_to_array(ParameterSets::fromUnserializedParameterSets([
            'one' => [
                'one' => 'hello',
            ],
            'two' => [
                'one' => 'goodbye',
            ],
        ]), true);
        self::assertEquals(['one', 'two'], array_keys($sets));
        $first = reset($sets);
        self::assertInstanceOf(ParameterSet::class, $first);
        assert($first instanceof ParameterSet);
        self::assertEquals('one', $first->getName());
        self::assertEquals([
            'one' => 'hello',
        ], $first->toUnserializedParameters());
    }
}
