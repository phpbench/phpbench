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

namespace PhpBench\Tests\Unit\Benchmark\Executor\benchmarks;

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
    public function provideParams()
    {
        return [
            [
                'hello' => 'goodbye',
            ],
        ];
    }
}
