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

namespace PhpBench\Extensions\XDebug\Tests\Unit\Executor;

use PhpBench\Benchmark\Remote\Launcher;
use PhpBench\Benchmark\Remote\Payload;
use PhpBench\Dom\Document;
use PhpBench\Extensions\XDebug\Converter\TraceToXmlConverter;
use PhpBench\Extensions\XDebug\Executor\TraceExecutor;
use PhpBench\Extensions\XDebug\Result\XDebugTraceResult;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Iteration;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\Result\MemoryResult;
use PhpBench\Model\Result\TimeResult;
use PhpBench\Model\Subject;
use PhpBench\Model\Variant;
use PhpBench\Registry\Config;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TraceExecutorTest extends TestCase
{
    public function setUp()
    {
        $this->launcher = $this->prophesize(Launcher::class);
        $this->filesystem = $this->prophesize(Filesystem::class);
        $this->converter = $this->prophesize(TraceToXmlConverter::class);

        $this->executor = new TraceExecutor(
            $this->launcher->reveal(),
            $this->converter->reveal(),
            $this->filesystem->reveal()
        );

        $options = new OptionsResolver();
        $this->executor->configure($options);
        $this->config = new Config('test', $options->resolve([]));

        $this->payload = $this->prophesize(Payload::class);
        $this->iteration = $this->prophesize(Iteration::class);
        $this->variant = $this->prophesize(Variant::class);
        $this->subject = $this->prophesize(Subject::class);
        $this->benchmark = $this->prophesize(Benchmark::class);
        $this->dom = $this->prophesize(Document::class);

        $this->iteration->getVariant()->willReturn($this->variant->reveal());
        $this->variant->getSubject()->willReturn($this->subject->reveal());
        $this->subject->getBenchmark()->willReturn($this->benchmark->reveal());
        $this->parameterSet = $this->prophesize(ParameterSet::class);
        $this->variant->getParameterSet()->willReturn($this->parameterSet->reveal());

        $this->benchmark->getClass()->willReturn('Test');
        $this->subject->getName()->willReturn('benchFoo');
        $this->parameterSet->getIndex()->willReturn(1);
    }

    /**
     * It should launch the payload.
     */
    public function testLaunchPayload()
    {
        $this->converter->convert(Argument::type('string'))->willReturn($this->dom->reveal());
        $this->dom->evaluate(
            'number(//entry[@function="Test->benchFoo"]/@end-time) - '.
            'number(//entry[@function="Test->benchFoo"]/@start-time)'
        )->willReturn(.012);

        $this->dom->evaluate(
            'number(//entry[@function="Test->benchFoo"]/@end-memory) - '.
            'number(//entry[@function="Test->benchFoo"]/@start-memory)'
        )->willReturn(100);

        $this->dom->evaluate('count(//entry[@function="Test->benchFoo"]//*)')->willReturn(5); //entry[@function="Test->benchFoo"]

        $this->payload->launch()->willReturn([
            'time' => 66,
            'mem' => [
                'peak' => 10,
                'real' => 10,
                'final' => 10,
            ],
        ]);
        $this->payload->mergePhpConfig([
            'xdebug.trace_output_name' => 'Test::benchFoo.P1',
            'xdebug.trace_output_dir' => 'xdebug',
            'xdebug.trace_format' => '1',
            'xdebug.auto_trace' => '1',
            'xdebug.coverage_enable' => '0',
            'xdebug.collect_params' => '3',
        ])->shouldBeCalled();

        $this->iteration->setResult(new TimeResult(66))->shouldBeCalled();
        $this->iteration->setResult(new MemoryResult(10, 10, 10))->shouldBeCalled();
        $this->iteration->setResult(Argument::type(XDebugTraceResult::class))->shouldBeCalled();

        $this->executor->launch(
            $this->payload->reveal(),
            $this->iteration->reveal(),
            $this->config
        );
    }

    /**
     * It should throw an exception if the trace file was not generated.
     * It should remove any existing trace file.
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage was not generated
     */
    public function testNoTraceGenerated()
    {
        $this->payload->launch()->willReturn([]);
        $this->payload->mergePhpConfig(Argument::type('array'))->shouldBeCalled();
        $this->filesystem->exists(Argument::any())->willReturn(true, false);
        $this->filesystem->remove(Argument::any())->shouldBeCalled();

        $this->executor->launch(
            $this->payload->reveal(),
            $this->iteration->reveal(),
            $this->config
        );
    }
}
