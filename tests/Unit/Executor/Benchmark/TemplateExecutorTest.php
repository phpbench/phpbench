<?php

namespace PhpBench\Tests\Unit\Executor\Benchmark;

use PhpBench\Benchmark\Remote\Launcher;
use PhpBench\Executor\Benchmark\TemplateExecutor;
use PhpBench\Executor\Exception\ExecutorScriptError;
use PhpBench\Model\Iteration;
use PhpBench\Registry\Config;
use PhpBench\Tests\IntegrationTestCase;
use PhpBench\Tests\Util\BenchmarkMetadataBuiler;
use PhpBench\Tests\Util\TestUtil;
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TemplateExecutorTest extends IntegrationTestCase
{
    public function testExceptionIfTimeNotSet(): void
    {
        $this->expectException(ExecutorScriptError::class);
        $template = <<<'EOT'
<?php

echo serialize([
]);
EOT;

        $this->execute($template, []);
    }

    public function testExceptionIfMemoryNotSet(): void
    {
        $this->expectException(ExecutorScriptError::class);
        $template = <<<'EOT'
<?php

echo serialize([
    'time' => 1234
]);
EOT;

        $this->execute($template, []);
    }

    public function testExceptionIfBufferedOutputPresent(): void
    {
        $this->expectException(ExecutorScriptError::class);
        $this->expectExceptionMessage('Benchmark made some noise');
        $template = <<<'EOT'
<?php

echo serialize([
    'time' => 1234,
    'mem' => [
        'peak' => 0,
        'final' => 0,
        'real' => 0,
    ],
    'buffer' => '123',
]);
EOT;

        $this->execute($template, []);
    }

    public function testRendererPathAndDoNotRemoveScript(): void
    {
        $template = <<<'EOT'
<?php

echo serialize([
    'time' => 1234,
    'mem' => [
        'peak' => 0,
        'final' => 0,
        'real' => 0,
    ],
]);
EOT;

        $this->execute($template, [
            TemplateExecutor::OPTION_PHP_RENDER_PATH => $this->workspace()->path('foobar'),
            TemplateExecutor::OPTION_PHP_REMOVE_SCRIPT => false,
        ]);

        $script = $this->workspace()->getContents('foobar');
        self::assertEquals($template, $script);
    }

    public function testExeceptionIfRenderPathIsADirectory(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('is not a file');
        $template = <<<'EOT'
<?php

echo serialize([
    'time' => 1234,
    'mem' => [
        'peak' => 0,
        'final' => 0,
        'real' => 0,
    ],
]);
EOT;

        $this->execute($template, [
            TemplateExecutor::OPTION_PHP_RENDER_PATH => $this->workspace()->path(),
            TemplateExecutor::OPTION_PHP_REMOVE_SCRIPT => false,
        ]);

        $script = $this->workspace()->getContents('foobar');
        self::assertEquals($template, $script);
    }

    public function execute(string $template, array $config): Iteration
    {
        $benchmarkMetadata = BenchmarkMetadataBuiler::create(__FILE__, 'TestBench')
            ->subject('barfoo')
            ->end()
            ->build();
        $subject = $benchmarkMetadata->getOrCreateSubject('barfoo');

        $suite = TestUtil::createSuite([
            'benchmarks' => ['Test'],
            'subjects' => ['test'],
        ]);

        $this->workspace()->put('/template', $template);

        $launcher = new Launcher();
        $executor = new TemplateExecutor($launcher, $this->workspace()->path('/template'));
        $optionsResolver = new OptionsResolver();
        $executor->configure($optionsResolver);
        $executor->execute(
            $subject,
            $iteration = $suite->findVariant('Test', 'test', '0')->createIteration([]),
            new Config('foo', $optionsResolver->resolve($config))
        );

        return $iteration;
    }
}
