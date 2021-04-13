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

use PhpBench\Remote\Exception\ScriptErrorException;
use PhpBench\Remote\Payload;
use PhpBench\Tests\TestCase;
use PhpBench\Tests\Unit\Executor\benchmarks\ParamProviderBench;

class ParameterSetExtractorTest extends TestCase
{
    public function testProvideParameterFromCallable(): void
    {
        $this->provideParams(['PhpBench\Tests\Unit\Executor\benchmarks\hello_world']);
    }

    public function testProvideParameterFromBenchmark(): void
    {
        $this->provideParams(['provideParams']);
    }

    public function testProvideParameterFromIterator(): void
    {
        $this->provideParams(['provideIterator']);
    }

    public function testProvideParameterFromGenerator(): void
    {
        $this->provideParams(['provideGenerator']);
    }

    public function testThrowsExceptionIfParameterDoesntExist(): void
    {
        $this->expectException(ScriptErrorException::class);
        $this->expectExceptionMessage('Class has no method "idontexist"');
        $this->provideParams(['idontexist']);
    }

    public function testThrowsExceptionIfMethodIsPrivate(): void
    {
        $this->expectException(ScriptErrorException::class);
        $this->expectExceptionMessage('Class has no method "privateParamProvider"');
        $this->provideParams(['privateParamProvider']);
    }

    private function provideParams(array $providers): void
    {
        $payload = new Payload(__DIR__ . '/../../../lib/Reflection/template/parameter_set_extractor.template', [
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
