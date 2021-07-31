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
        ])->toUnwrappedParameters());
    }

    public function testFromWrappedParameters(): void
    {
        self::assertEquals([
            'foo' => 'hello',
            'bar' => 'goodbye',
        ], ParameterSet::fromSerializedParameters('test', [
            'foo' => serialize('hello'),
            'bar' => serialize('goodbye')
        ])->toUnwrappedParameters());
    }

    public function testFromUnwrappedParameters(): void
    {
        self::assertEquals([
            'foo' => 'hello',
            'bar' => 'goodbye',
        ], ParameterSet::fromUnwrappedParameters('test', [
            'foo' => 'hello',
            'bar' => 'goodbye',
        ])->toUnwrappedParameters());
    }

    public function testToSerializedParameters(): void
    {
        self::assertEquals([
            'foo' => serialize('hello'),
            'bar' => serialize('goodbye'),
        ], ParameterSet::fromUnwrappedParameters('test', [
            'foo' => 'hello',
            'bar' => 'goodbye',
        ])->toSerializedParameters());
    }
}
