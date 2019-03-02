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

namespace PhpBench\Tests\Unit\Executor;

use PhpBench\Benchmark\Remote\Exception\ScriptErrorException;
use PhpBench\Benchmark\Remote\Payload;
use PhpBench\Tests\Unit\Executor\benchmarks\ParamProviderBench;
use PHPUnit\Framework\TestCase;

class ParameterSetExtractorTest extends TestCase
{
    public function testProvideParameterFromCallable()
    {
        $this->provideParams(['PhpBench\Tests\Unit\Executor\benchmarks\hello_world']);
    }

    public function testProvideParameterFromBenchmark()
    {
        $this->provideParams(['provideParams']);
    }

    public function testProvideParameterFromIterator()
    {
        $this->provideParams(['provideIterator']);
    }

    public function testProvideParameterFromGenerator()
    {
        $this->provideParams(['provideGenerator']);
    }

    public function testThrowsExceptionIfParameterDoesntExist()
    {
        $this->expectException(ScriptErrorException::class);
        $this->expectExceptionMessage('Class has no method "idontexist"');
        $this->provideParams(['idontexist']);
    }

    public function testThrowsExceptionIfMethodIsPrivate()
    {
        $this->expectException(ScriptErrorException::class);
        $this->expectExceptionMessage('Class has no method "privateParamProvider"');
        $this->provideParams(['privateParamProvider']);
    }

    private function provideParams(array $providers)
    {
        $payload = new Payload(__DIR__ . '/../../../lib/Benchmark/Remote/template/parameter_set_extractor.template', [
            'bootstrap' => __DIR__ . '/benchmarks/ParamProviderBench.php',
            'file' => __DIR__ . '/benchmarks/ParamProviderBench.php',
            'class' => ParamProviderBench::class,
            'paramProviders' => json_encode($providers),
        ]);
        $result = $payload->launch();
        $this->assertEquals([
            [
                ['hello' => 'goodbye'],
            ],
        ], $result);
    }
}
