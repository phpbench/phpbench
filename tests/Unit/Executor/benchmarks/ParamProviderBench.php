<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace PhpBench\Tests\Unit\Executor\benchmarks;

use ArrayIterator;
use Generator;
use Iterator;

function hello_world()
{
    return [
        [
            'hello' => 'goodbye',
        ],
    ];
}

class ParamProviderBench
{
    private function privateParamProvider(): void
    {
    }

    public function paramProviderNotArray(): array
    {
        return [
            'foo' => 'bar',
        ];
    }

    public function provideParams()
    {
        return [
            [
                'hello' => 'goodbye',
            ],
        ];
    }

    public function provideGenerator(): Generator
    {
        yield [ 'hello' => 'goodbye' ];
    }

    public function provideIterator(): Iterator
    {
        return new ArrayIterator(
            [
                [ 'hello' => 'goodbye' ]
            ]
        );
    }

    public function provideIteratorWithKeys(): Iterator
    {
        return new ArrayIterator(
            [
                'one' => [ 'hello' => 'goodbye' ]
            ]
        );
    }
}
