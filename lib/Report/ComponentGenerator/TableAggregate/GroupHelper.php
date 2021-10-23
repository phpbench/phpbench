<?php

namespace PhpBench\Report\ComponentGenerator\TableAggregate;

use RuntimeException;

class GroupHelper
{
    const DEFAULT_GROUP_NAME = 'default';

    /**
     * @param array<string,int> $colSizes Ordered column definition sizes
     * @param array<string,string> $groupNameByColumn
     * @return list<array{string,int}>
     */
    public static function resolveGroupSizes(array $colSizes, array $groupNameByColumn): array
    {
        if (empty($colSizes)) {
            return [];
        }
        $groups = [];
        $groupSize = 0;
        $lastGroup = null;
        $finishedGroups = [];
        
        foreach ($colSizes as $defName => $colSize) {
            $groupName = $groupNameByColumn[$defName] ?? self::DEFAULT_GROUP_NAME;

            if (null === $lastGroup) {
                $lastGroup = $groupName;
                $groupSize = $colSize;
                continue;
            }
        
            if ($lastGroup === $groupName) {
                $groupSize += $colSize;
                continue;
            }
        
            $groups[] = [$lastGroup, (int)$groupSize];
            $groupSize = $colSize;
            $lastGroup = $groupName;
        }
        
        if ($groupSize) {
            $groups[] = [$lastGroup, (int)$groupSize];
        }

        return $groups;
    }
}
