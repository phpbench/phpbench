<?php

namespace PhpBench\Tests\Unit\Model;

use PhpBench\Model\ParameterSetsCollection;
use PHPUnit\Framework\TestCase;

class ParameterSetsCollectionTest extends TestCase
{
    public function testFromWrappedParameterSetsCollection(): void
    {
        $set = ParameterSetsCollection::fromWrappedParameterSetsCollection([
            [
                'one' => [
                    'k1' => [
                        'type' => 'string',
                        'value' => serialize('hello'),
                    ]
                ]
            ]
        ]);

        self::assertEquals([
            [
                'one' => [
                    'k1' => 'hello',
                ],
            ]
        ], $set->toUnwrappedParameterSetsCollection());
    }

    public function testFromUnwrappedParameterSetsCollection(): void
    {
        $set = ParameterSetsCollection::fromUnwrappedParameterSetsCollection([
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
        ], $set->toUnwrappedParameterSetsCollection());
    }
}
