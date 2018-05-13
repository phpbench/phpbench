<?php

namespace PhpBench\Tests\Unit\Benchmark\Executor;

use PHPUnit\Framework\TestCase;
use PhpBench\Benchmark\Remote\Payload;
use PhpBench\Tests\Unit\Benchmark\Executor\benchmarks\ParamProviderBench;

class ParameterSetExtractorTest extends TestCase
{
    public function testProvideParameterFromCallable()
    {
        $this->provideParams(['PhpBench\Tests\Unit\Benchmark\Executor\benchmarks\hello_world']);
    }

    public function testProvideParameterFromBenchmark()
    {
        $this->provideParams(['provideParams']);
    }

    private function provideParams(array $providers)
    {
        $payload = new Payload(__DIR__ . '/../../../../lib/Benchmark/Remote/template/parameter_set_extractor.template', [
            'bootstrap' => __DIR__ . '/benchmarks/ParamProviderBench.php',
            'file' => __DIR__ . '/benchmarks/ParamProviderBench.php',
            'class' => ParamProviderBench::class,
            'paramProviders' => json_encode($providers),
        ]);
        $result = $payload->launch();
        $this->assertEquals([
            [
                [ 'hello' => 'goodbye' ]
            ]
        ], $result);
    }
}
