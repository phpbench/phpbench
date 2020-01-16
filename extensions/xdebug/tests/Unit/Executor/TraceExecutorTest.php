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

use PhpBench\Benchmark\Metadata\SubjectMetadata;
use PhpBench\Benchmark\Remote\Payload;
use PhpBench\Dom\Document;
use PhpBench\Executor\Benchmark\TemplateExecutor;
use PhpBench\Extensions\XDebug\Converter\TraceToXmlConverter;
use PhpBench\Extensions\XDebug\Executor\TraceExecutor;
use PhpBench\Extensions\XDebug\Result\XDebugTraceResult;
use PhpBench\Model\Benchmark;
use PhpBench\Model\Iteration;
use PhpBench\Model\ParameterSet;
use PhpBench\Model\Subject;
use PhpBench\Model\Variant;
use PhpBench\Registry\Config;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TraceExecutorTest extends TestCase
{
    /**
     * @var ObjectProphecy
     */
    private $templateExecutor;
    /**
     * @var ObjectProphecy
     */
    private $filesystem;
    /**
     * @var ObjectProphecy
     */
    private $converter;
    /**
     * @var TraceExecutor
     */
    private $executor;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var ObjectProphecy
     */
    private $payload;
    /**
     * @var ObjectProphecy
     */
    private $iteration;
    /**
     * @var ObjectProphecy
     */
    private $variant;
    /**
     * @var ObjectProphecy
     */
    private $subjectMetadata;
    /**
     * @var ObjectProphecy
     */
    private $benchmark;
    /**
     * @var ObjectProphecy
     */
    private $dom;
    /**
     * @var ObjectProphecy
     */
    private $parameterSet;

    protected function setUp(): void
    {
        $this->templateExecutor = $this->prophesize(TemplateExecutor::class);
        $this->filesystem = $this->prophesize(Filesystem::class);
        $this->converter = $this->prophesize(TraceToXmlConverter::class);

        $this->executor = new TraceExecutor(
            $this->templateExecutor->reveal(),
            $this->converter->reveal(),
            $this->filesystem->reveal()
        );

        $options = new OptionsResolver();
        $this->executor->configure($options);
        $this->config = new Config('test', array_merge([
            'php_config' => [],
        ], $options->resolve([])));

        $this->payload = $this->prophesize(Payload::class);
        $this->iteration = $this->prophesize(Iteration::class);
        $this->variant = $this->prophesize(Variant::class);
        $this->subject = $this->prophesize(Subject::class);
        $this->subjectMetadata = $this->prophesize(SubjectMetadata::class);
        $this->benchmark = $this->prophesize(Benchmark::class);
        $this->dom = $this->prophesize(Document::class);

        $this->iteration->getVariant()->willReturn($this->variant->reveal());
        $this->variant->getSubject()->willReturn($this->subject->reveal());
        $this->subject->getBenchmark()->willReturn($this->benchmark->reveal());
        $this->subject->getName()->willReturn('benchFoo');
        $this->parameterSet = $this->prophesize(ParameterSet::class);
        $this->variant->getParameterSet()->willReturn($this->parameterSet->reveal());

        $this->benchmark->getClass()->willReturn('Test');
        $this->parameterSet->getIndex()->willReturn(1);
    }

    public function testExecute()
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

        $this->templateExecutor->execute($this->subjectMetadata->reveal(), $this->iteration->reveal(), $this->config)->shouldBeCalled();
        $this->iteration->setResult(Argument::type(XDebugTraceResult::class))->shouldBeCalled();

        $this->executor->execute(
            $this->subjectMetadata->reveal(),
            $this->iteration->reveal(),
            $this->config
        );
    }

    /**
     * It should throw an exception if the trace file was not generated.
     * It should remove any existing trace file.
     *
     */
    public function testNoTraceGenerated()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('was not generated');
        $this->filesystem->exists(Argument::any())->willReturn(false, false);

        $this->executor->execute(
            $this->subjectMetadata->reveal(),
            $this->iteration->reveal(),
            $this->config
        );
    }
}
