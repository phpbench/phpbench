<?php

namespace PhpBench\Tests\Unit\Expression;

use Closure;
use Generator;
use PHPUnit\Framework\TestCase;
use PhpBench\Expression\MustacheRenderer;
use PhpBench\Tests\IntegrationTestCase;

class MustacheRendererTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideTemplate
     */
    public function testRenderTemplate(string $template, Closure $closure, string $expected): void
    {
        self::assertEquals($expected, (new MustacheRenderer())->render($template, $closure));
    }

    /**
     * @return Generator<mixed>
     */
    public function provideTemplate(): Generator
    {
        yield 'empty' => [
            '',
            function () {},
            ''
        ];
        yield 'nothing' => [
            'foobar',
            function () {},
            'foobar'
        ];
        yield 'single' => [
            'foobar {{ hello }}',
            function (string $expression) {
                return 'world' . ' ' . $expression;
            },
            'foobar world hello'
        ];
        yield 'multiple' => [
            'foobar {{ hello }} {{ goodbye }} {{ and good }}',
            function (string $expression) {
                return $expression;
            },
            'foobar hello goodbye and good'
        ];
    }
}
