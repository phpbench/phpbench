<?php

namespace PhpBench\Tests\Benchmark\Console\Command;

use PhpBench\Console\Command\RunCommand;
use PhpBench\Extension\ConsoleExtension;
use PhpBench\Extension\RunnerExtension;
use PhpBench\Tests\Benchmark\IntegrationBenchCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @BeforeMethods("setUp")
 *
 * @Revs(1)
 *
 * @Iterations(10)
 */
class RunCommandBench extends IntegrationBenchCase
{
    public function setUp(): void
    {
        $this->createExample();
        $this->workspace()->reset();
    }

    public function benchDefault(): void
    {
        $this->runCommand([
            'path' => '.',
        ]);
    }

    public function benchInBand(): void
    {
        $this->runCommand([
            'path' => '.',
            '--executor' => 'local',
        ]);
    }

    public function benchNoEnv(): void
    {
        $this->runCommand([
            'path' => '.',
            '--executor' => 'local',
        ], [
            RunnerExtension::PARAM_ENABLED_PROVIDERS => [],
        ]);
    }

    private function createExample(): void
    {
        $this->workspace()->put(
            'NothingBench.php',
            <<<'EOT'
<?php
namespace PhpBench\Tests\Workspace;

class NothingBench { public function benchNothing(): void {}}
EOT
        );
    }

    private function runCommand(array $args, array $config = []): void
    {
        chdir($this->workspace()->path());
        $command = $this->container(array_merge([
            ConsoleExtension::PARAM_DISABLE_OUTPUT => false,
        ], $config))->get(RunCommand::class);
        $cwd = getcwd();
        $input = new ArrayInput($args);
        $output = new BufferedOutput();
        Assert::assertEquals(0, $command->run($input, $output), 'Command exited successfully');
        chdir($cwd);
    }
}
