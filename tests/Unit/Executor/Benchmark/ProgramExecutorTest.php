<?php

namespace PhpBench\Tests\Unit\Executor\Benchmark;

use PHPUnit\Framework\TestCase;
use PhpBench\Executor\Benchmark\ProgramExecutor;
use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\ExecutionResults;
use PhpBench\Executor\Parser\UnitParser;
use PhpBench\Executor\ScriptBuilder;
use PhpBench\Executor\ScriptExecutor;
use PhpBench\Executor\ScriptExecutorInterface;
use PhpBench\Executor\Unit\TestUnit;
use PhpBench\Model\MainResultFactory;
use PhpBench\Model\Result\BufferResult;
use PhpBench\Model\Result\BufferResultFactory;
use PhpBench\Registry\Config;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProgramExecutorTest extends TestCase
{
    /**
     * @var ScriptExecutorInterface
     */
    private $executor;

    /**
     * @var array
     */
    private $units;


    public function setUp(): void
    {
        $this->executor = $this->createMock(ScriptExecutorInterface::class);
        $this->units = [
            new TestUnit('root', 'rootstart', 'rootend'),
            new TestUnit('one', 'onestart', 'oneend'),
        ];
    }

    public function testExecutor(): void 
    {
        $this->expectExecutorWith('onestart', [
            'buffer' => [
                'buffer' => 'start',
            ],
        ]);

        $results = $this->execute(
            new ExecutionContext('foo', 'path', 'method'),
            [
                'program' => [
                    'one',
                ],
            ],
        );

        self::assertEquals(ExecutionResults::fromResults(
            new BufferResult('start'),
        ), $results);
    }

    private function execute(ExecutionContext $context, array $config): ExecutionResults
    {
        $factory = new MainResultFactory([
            'buffer' => new BufferResultFactory(),
        ]);
        $parser = new UnitParser();
        $builder = new ScriptBuilder($this->units);
        $executor = new ProgramExecutor($parser, $builder, $this->executor, $factory);

        $resolver = new OptionsResolver();
        $executor->configure($resolver);
        $config = $resolver->resolve($config);

        return $executor->execute($context, new Config('foo', $config));
    }

    private function expectExecutorWith(string $string, array $result)
    {
        $this->executor->method('execute')->with($this->stringContains($string))->willReturn($result);
    }
}
