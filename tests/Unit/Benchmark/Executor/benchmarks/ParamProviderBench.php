<?php

namespace PhpBench\Tests\Unit\Benchmark\Executor\benchmarks;

function hello_world()
{
    return [
        [
            'hello' => 'goodbye',
        ]
    ];
}

class ParamProviderBench
{
    public function provideParams()
    {
        return [
            [
                'hello' => 'goodbye',
            ]
        ];
    }
}
