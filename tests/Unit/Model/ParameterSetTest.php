<?php

namespace PhpBench\Tests\Unit\Model;

use Generator;
use PhpBench\Model\ParameterContainer;
use PhpBench\Model\ParameterSet;
use PHPUnit\Framework\TestCase;

class ParameterSetTest extends TestCase
{
    public function testFromParameterContainers(): void
    {
        self::assertEquals([
            'foo' => 'hello',
            'bar' => 'goodbye',
        ], ParameterSet::fromParameterContainers('test', [
            'foo' => ParameterContainer::fromValue('hello'),
            'bar' => ParameterContainer::fromValue('goodbye'),
        ])->toUnserializedParameters());
    }

    public function testFromWrappedParameters(): void
    {
        self::assertEquals([
            'foo' => 'hello',
            'bar' => 'goodbye',
        ], ParameterSet::fromSerializedParameters('test', [
            'foo' => serialize('hello'),
            'bar' => serialize('goodbye')
        ])->toUnserializedParameters());
    }

    public function testFromUnserializedParameters(): void
    {
        self::assertEquals([
            'foo' => 'hello',
            'bar' => 'goodbye',
        ], ParameterSet::fromUnserializedValues('test', [
            'foo' => 'hello',
            'bar' => 'goodbye',
        ])->toUnserializedParameters());
    }

    public function testToSerializedParameters(): void
    {
        self::assertEquals([
            'foo' => serialize('hello'),
            'bar' => serialize('goodbye'),
        ], ParameterSet::fromUnserializedValues('test', [
            'foo' => 'hello',
            'bar' => 'goodbye',
        ])->toSerializedParameters());
    }

    /**
     * @dataProvider provideMatches
     */
    public function testMatches(string $name, array $patterns, bool $shouldMatch): void
    {
        $set = ParameterSet::fromUnserializedValues($name, []);
        self::assertEquals($shouldMatch, $set->nameMatches($patterns));
    }

    /**
     * @return Generator<mixed>
     */
    public function provideMatches(): Generator
    {
        yield 'empty string and empty patterns always matches' => ['', [], true];

        yield 'empty list of patterns always matches' => ['one', [], true];

        yield 'full match' => ['one', ['one'], true];

        yield 'partial match start' => ['one two', ['one'], true];

        yield 'partial match end' => ['one two', ['two'], true];

        yield 'non match' => ['one two', ['twothree'], false];

        yield 'regex non-match' => ['one two', ['^one$'], false];

        yield 'regex match' => ['one two', ['^one two$'], true];
    }
}
