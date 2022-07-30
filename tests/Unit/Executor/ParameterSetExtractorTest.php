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
        $result = $this->provideParams(['PhpBench\Tests\Unit\Executor\benchmarks\hello_world']);
        $this->assertEquals([
            [
                ['hello' => serialize('goodbye')],
            ],
        ], $result);
    }

    public function testProvideParameterFromBenchmark(): void
    {
        $result = $this->provideParams(['provideParams']);
        $this->assertEquals([
            [
                ['hello' => serialize('goodbye')],
            ],
        ], $result);
    }

    public function testProvideParameterFromIterator(): void
    {
        $result = $this->provideParams(['provideIterator']);
        $this->assertEquals([
            [
                ['hello' => serialize('goodbye')],
            ],
        ], $result);
    }

    public function testProvideParameterFromIteratorWithKeys(): void
    {
        $result = $this->provideParams(['provideIteratorWithKeys']);
        $this->assertEquals([
            [
                'one' => ['hello' => serialize('goodbye')],
            ],
        ], $result);
    }

    public function testProvideParameterFromGenerator(): void
    {
        $result = $this->provideParams(['provideGenerator']);
        $this->assertEquals([
            [
                ['hello' => serialize('goodbye')],
            ],
        ], $result);
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

    public function testThrowsExceptionIfParameterProviderDoesNotReturnArray(): void
    {
        $this->expectException(ScriptErrorException::class);
        $this->expectExceptionMessage('Parameters in set "foo" must be an array');
        $this->provideParams(['paramProviderNotArray']);
    }

    /**
     * @param string[] $providers
     */
    private function provideParams(array $providers): array
    {
        $payload = new Payload(__DIR__ . '/../../../lib/Reflection/template/parameter_set_extractor.template', [
            'bootstrap' => __DIR__ . '/benchmarks/ParamProviderBench.php',
            'file' => __DIR__ . '/benchmarks/ParamProviderBench.php',
            'class' => ParamProviderBench::class,
            'paramProviders' => json_encode($providers),
        ]);

        return $payload->launch();
    }
}
