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

namespace PhpBench\Extensions\XDebug\Tests\Unit;

use DTL\Invoke\Invoke;
use PhpBench\Executor\ExecutionContext;
use PhpBench\Extensions\XDebug\XDebugUtil;
use PhpBench\Model\Iteration;
use PhpBench\Tests\TestCase;

class XDebugUtilTest extends TestCase
{
    private $iteration;
    private $subject;
    private $benchmark;
    private $parameters;

    /**
     * It should generate a filename for an iteration.
     *
     * @dataProvider provideGenerate
     */
    public function testGenerate($class, $subject, $expected): void
    {
        $params = [
            'classPath' => '/foobar',
            'parameterSetName' => '7',
            'parameters' => ['asd'],
            'className' => $class,
            'methodName' => $subject,
        ];
        $xdebugUtil = new XDebugUtil('3.1.2', false);

        $result = $xdebugUtil->filenameFromContext(Invoke::new(ExecutionContext::class, $params));
        $this->assertEquals(
            $expected,
            $result
        );
    }

    /**
     *
     * @dataProvider provideXdebugVersion
     */
    public function testCacheGrindExtension($xdebugVersion, $useCompression, $expectedExtension): void
    {
        $xdebugUtil = new XDebugUtil($xdebugVersion, $useCompression);

        $cacheGrindExtension = $xdebugUtil->getCachegrindExtensionOfGeneratedFile();

        $this->assertEquals($expectedExtension, $cacheGrindExtension);
    }

    public function provideGenerate()
    {
        return [
            [
                'Benchmark',
                'Subject',
                '2214b023e25587e253082262814e6c37'
            ],
            [
                'Benchmark\\Foo',
                'Subject\\//asd',
                '25133125bf4eca7a08502711d2c8403d'
            ],
        ];
    }

    public function provideXdebugVersion(): array
    {
        return [
            ['2.8.1', false, '.cachegrind'],
            ['3.1.2', false, '.cachegrind'],
            ['3.1.2', true, '.cachegrind.gz']
        ];
    }
}
