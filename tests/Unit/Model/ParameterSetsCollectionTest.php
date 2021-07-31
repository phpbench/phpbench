<?php

namespace PhpBench\Tests\Unit\Model;

use PhpBench\Model\ParameterSetsCollection;
use PHPUnit\Framework\TestCase;

class ParameterSetsCollectionTest extends TestCase
{
    public function testFromWrappedParameterSetsCollection(): void
    {
        $set = ParameterSetsCollection::fromSerializedParameterSetsCollection([
            [
                'one' => [
                    'k1' => serialize('hello'),
                ]
            ]
        ]);

        self::assertEquals([
            [
                'one' => [
                    'k1' => 'hello',
                ],
            ]
        ], $set->toUnserializedParameterSetsCollection());
    }

    public function testFromUnserializedParameterSetsCollection(): void
    {
        $set = ParameterSetsCollection::fromUnserializedParameterSetsCollection([
            [
                'one' => [
                    'k1' => 'hello',
                ]
            ]
        ]);

        self::assertEquals([
            [
                'one' => [
                    'k1' => 'hello',
                ],
            ]
        ], $set->toUnserializedParameterSetsCollection());
    }
}
