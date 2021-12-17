<?php

namespace PhpBench\Tests\Unit\Executor\Unit;

use PhpBench\Executor\ExecutionContext;
use PhpBench\Executor\Parser\UnitParser;
use PhpBench\Executor\PhpProcessFactory;
use PhpBench\Executor\PhpProcessOptions;
use PhpBench\Executor\ScriptBuilder;
use PhpBench\Executor\ScriptExecutor;
use PhpBench\Executor\Unit\RootUnit;
use PhpBench\Tests\IntegrationTestCase;
use PhpBench\Tests\TestCase;
use PhpBench\Tests\Util\ExecutionContextBuilder;

class UnitTestCase extends IntegrationTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }
    protected function executeProgram(array $units, ExecutionContext $context, array $program)
    {
        $parser = new UnitParser();
        $node = $parser->parse($program);
        $units['root'] = new RootUnit(null);
        $builder = new ScriptBuilder($units);
        $executor = new ScriptExecutor(
            new PhpProcessFactory(new PhpProcessOptions()),
            $this->workspace()->path('example'),
            false
        );
        return $executor->execute($builder->build($context, $node));
    }

    protected function context(): ExecutionContextBuilder
    {
        return ExecutionContextBuilder::create()
            ->withBenchmarkClass(ExampleClass::class)
            ->withBenchmarkPath(__DIR__ . '/ExampleClass.php')
            ->withMethodName('register');
    }

    public function registeredCalls(): array
    {
        if (!$this->workspace()->exists('example.bench')) {
            return [];
        }
        $calls = trim($this->workspace()->getContents('example.bench'));
        return array_map(function (string $json) {
            return json_decode($json, true);
        }, explode("\n", $calls));
    }
}
