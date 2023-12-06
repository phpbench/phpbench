<?php

namespace PhpBench\Tests\Unit\Report\ComponentGenerator\TableAggregate;

use Generator;
use PHPUnit\Framework\TestCase;
use PhpBench\Report\ComponentGenerator\TableAggregate\GroupHelper;

class GroupHelperTest extends TestCase
{
    /**
     * @dataProvider provideResolveGroupSizes
     *
     * @param array<string, int> $colSizes
     * @param array<string, string> $groupNameByColumn
     * @param list<array{string,int}> $expectedGroupSizes
     */
    public static function testResolveGroupSizes(array $colSizes, array $groupNameByColumn, array $expectedGroupSizes): void
    {
        $resolt = GroupHelper::resolveGroupSizes($colSizes, $groupNameByColumn);
        self::assertEquals($expectedGroupSizes, $resolt);
    }

    /**
     * @return Generator<list{array<string, int>, array<string, string>, list<array{string,int}>}>
     */
    public static function provideResolveGroupSizes(): Generator
    {
        yield 'empty' => [
            [],
            ['one' => 'group_1'],
            []
        ];

        yield '1 col in a group' => [
            ['one' => 1],
            ['one' => 'group_1'],
            [
                ['group_1', 1],
            ]
        ];

        yield [
            ['one' => 2],
            ['one' => 'group_1'],
            [
                ['group_1', 2],
            ]
        ];

        yield [
            ['one' => 2, 'three' => 2],
            ['one' => 'group_1'],
            [
                ['group_1', 2],
                [GroupHelper::DEFAULT_GROUP_NAME, 2],
            ]
        ];

        yield [
            ['one' => 2, 'three' => 2],
            ['one' => 'group_1'],
            [
                ['group_1', 2],
                [GroupHelper::DEFAULT_GROUP_NAME, 2],
            ]
        ];

        yield [
            ['one' => 2, 'three' => 2, 'two' => 2],
            ['one' => 'group_1', 'two' => 'group_1'],
            [
                ['group_1', 2],
                [GroupHelper::DEFAULT_GROUP_NAME, 2],
                ['group_1', 2],
            ]
        ];
    }
}
