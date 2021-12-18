<?php

namespace PhpBench\Tests\Benchmark;

use Generator;

class RegressionBench
{
    /**
     * @ParamProviders({"provideNoOp"})
     */
    public function benchNoOp($params): void
    {
    }

    /**
     * @ParamProviders({"provideSleep"})
     */
    public function benchSleep(array $params): void
    {
        usleep($params['sleep']);
    }

    public function provideNoOp(): Generator
    {
        yield [
            0
        ];
    }

    public function provideSleep(): Generator
    {
        foreach ([
            0,
            50,
            100,
            500,
            1000,
            5000,
            10000,
            20000,
            50000,
            100000,
        ] as $sleep) {
            yield [
                'sleep' => $sleep
            ];
        }
    }
}
