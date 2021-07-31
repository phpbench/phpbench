<?php

namespace PhpBench\Tests\Unit\Model;

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
}
