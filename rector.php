<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\FuncCall\UnwrapSprintfOneArgumentRector;
use Rector\CodingStyle\Rector\ArrowFunction\StaticArrowFunctionRector;
use Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Rector\Config\RectorConfig;
use Rector\Php84\Rector\Param\ExplicitNullableParamTypeRector;

return RectorConfig::configure()
    ->withImportNames()
    ->withPaths([
        __DIR__ . '/lib',
        __DIR__ . '/tests',
        __DIR__ . '/examples',
    ])
    ->withRules([
        StaticArrowFunctionRector::class,
        EncapsedStringsToSprintfRector::class,
        UnwrapSprintfOneArgumentRector::class,
        ExplicitNullableParamTypeRector::class,
    ]);
