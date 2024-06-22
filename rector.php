<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector;
use Rector\CodeQuality\Rector\FuncCall\UnwrapSprintfOneArgumentRector;
use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Rector\CodingStyle\Rector\ArrowFunction\StaticArrowFunctionRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\Config\RectorConfig;
use Rector\Php53\Rector\Ternary\TernaryToElvisRector;
use Rector\Php73\Rector\FuncCall\JsonThrowOnErrorRector;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->parallel();
    $rectorConfig->importNames();
    $rectorConfig->importShortClasses();

    $rectorConfig->paths([
        __DIR__ . '/examples',
        __DIR__ . '/lib',
        __DIR__ . '/tests',
    ]);

    $rectorConfig->skip([
        TernaryToElvisRector::class,
        JsonThrowOnErrorRector::class,
        ExplicitBoolCompareRector::class,
        ClosureToArrowFunctionRector::class,
        UnusedForeachValueToArrayKeysRector::class,
        FlipTypeControlToUseExclusiveTypeRector::class,
    ]);

    $rectorConfig->rules([
        StaticArrowFunctionRector::class,
        EncapsedStringsToSprintfRector::class,
        UnwrapSprintfOneArgumentRector::class,
    ]);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_81,
    ]);
};
