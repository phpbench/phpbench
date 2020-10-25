<?php

namespace PhpBench\Tests;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class TestCase extends PHPUnitTestCase
{
    use ProphecyTrait;

    public static function assertMatchesRegularExpression(string $pattern, string $string, string $message = ''): void
    {
        if (method_exists(parent::class, 'assertMatchesRegularExpression')) {
            parent::assertMatchesRegularExpression($pattern, $string, $message);

            return;
        }

        self::assertRegExp($pattern, $string, $message);
    }
}
