<?php

namespace PhpBench\Examples\Benchmark\Micro;

use DateTime;

class ComplexParameterBench
{
    /**
     * @ParamProviders("provideParameter")
     */
    public function benchParameter(array $params): void
    {
    }

    public function provideParameter(): array
    {
        return [
            [
                'param' => "\x80"
            ],
            [
                'param' => new DateTime()
            ]
        ];
    }
}
