<?php

namespace PhpBench\Tests\Unit\Util;

use Generator;
use PhpBench\Tests\IntegrationTestCase;
use PhpBench\Util\PathNormalizer;

class PathUtilTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideNormalizePaths
     */
    public function testNormalizePath(string $baseDir, array $paths, array $expected): void
    {
        self::assertPaths($expected, PathNormalizer::normalizePaths($baseDir, $paths));
    }

    /**
     */
    public static function provideNormalizePaths(): Generator
    {
        yield [
            '',
            ['foobar'],
            ['foobar'],
        ];

        yield [
            '/home/daniel',
            ['foobar'],
            ['/home/daniel/foobar'],
        ];

        yield 'absolute' => [
            '/home/daniel',
            ['/bar/foobar'],
            ['/bar/foobar'],
        ];
    }

    public function testGlobWithRelativePaths(): void
    {
        $this->workspace()->put('foobar/baz/foo', '1');
        $this->workspace()->put('foobar/bom/foo', '2');

        self::assertPaths([
            $this->workspace()->path('foobar/bom/foo'),
            $this->workspace()->path('foobar/baz/foo'),
        ], PathNormalizer::normalizePaths($this->workspace()->path(), [
            'foobar/*/foo',
        ]));
    }

    public function testGlobWithAbsolutePaths(): void
    {
        $this->workspace()->put('foobar/baz/foo', '1');
        $this->workspace()->put('foobar/bom/foo', '2');

        self::assertPaths([
            $this->workspace()->path('foobar/bom/foo'),
            $this->workspace()->path('foobar/baz/foo'),
        ], PathNormalizer::normalizePaths($this->workspace()->path(), [
            $this->workspace()->path('foobar/*/foo'),
        ]));
    }

    private static function assertPaths(array $paths, array $expected): void
    {
        sort($paths);
        sort($expected);
        self::assertEquals($expected, $paths);
    }
}
