<?php

namespace PhpBench\Tests\Unit\Model;

use function iterator_to_array;
use PhpBench\Model\Exception\InvalidParameterSets;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\ParameterSets;
use PHPUnit\Framework\TestCase;

class ParameterSetsTest extends TestCase
{
    public function testInvalidParametersWrapped(): void
    {
        $this->expectException(InvalidParameterSets::class);
        $this->expectExceptionMessage('Each parameter set must be an array, got "string"');
        /** @phpstan-ignore-next-line */
        ParameterSets::fromWrappedParameterSets(['asd' => 'bar']);
    }

    public function testInvalidParametersUnwrapped(): void
    {
        $this->expectException(InvalidParameterSets::class);
        $this->expectExceptionMessage('Each parameter set must be an array, got "string"');
        /** @phpstan-ignore-next-line */
        ParameterSets::fromUnwrappedParameterSets(['asd' => 'bar']);
    }

    public function testIteratesWithUnsafeSetNamesAsKeys(): void
    {
        $sets = iterator_to_array(ParameterSets::fromWrappedParameterSets([
            'one' => [
                'one' => [
                    'type' => 'string',
                    'value' => serialize('hello'),
                ]
            ],
            'two' => [
                'one' => [
                    'type' => 'string',
                    'value' => serialize('hello'),
                ]
            ],
        ]), true);
        self::assertEquals(['one', 'two'], array_keys($sets));
        $first = reset($sets);
        self::assertInstanceOf(ParameterSet::class, $first);
        assert($first instanceof ParameterSet);
        self::assertEquals('one', $first->getName());
        self::assertEquals([
            'one' => 'hello',
        ], $first->toUnwrappedParameters());
    }

    public function testIteratesWithSetNamesAsKeys(): void
    {
        $sets = iterator_to_array(ParameterSets::fromUnwrappedParameterSets([
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
        ], $first->toUnwrappedParameters());
    }
}
