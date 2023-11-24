<?php

namespace PhpBench\Tests\Example;

use Generator;
use PhpBench\Console\Application;
use PhpBench\Extension\ConsoleExtension;
use PhpBench\Extension\RunnerExtension;
use PhpBench\PhpBench;
use PhpBench\Tests\IntegrationTestCase;
use PhpBench\Tests\Util\Approval;
use RuntimeException;
use Symfony\Component\Console\Input\StringInput;

use function json_last_error_msg;

class CommandsTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        $this->workspace()->reset();
    }

    /**
     * @dataProvider provideCommand
     */
    public function testCommand(string $path): void
    {
        $this->createExample();

        $approval = Approval::create($path, 3);
        $commands = array_map(function (string $command) {
            if (0 !== strpos($command, 'phpbench')) {
                throw new RuntimeException(sprintf(
                    'Command test command must start with `phpbench`, got "%s"',
                    $command
                ));
            }

            return substr($command, strlen('phpbench '));
        }, explode("\n", trim($approval->getSection(1))));

        $jsonString = $approval->getSection(0);
        $decoded = json_decode($jsonString, true);

        if (null === $decoded) {
            throw new RuntimeException(sprintf('Could not decode JSON: %s: %s', $jsonString, json_last_error_msg()));
        }
        $this->workspace()->put('phpbench.json', json_encode(array_merge([
            RunnerExtension::PARAM_ENABLED_PROVIDERS => [],
            ConsoleExtension::PARAM_OUTPUT_STREAM => $this->workspace()->path('output'),
            ConsoleExtension::PARAM_ERROR_STREAM => 'php://temp',
        ], $decoded)));

        foreach ($commands as $command) {
            $input = new StringInput($command);
            $container = PhpBench::loadContainer($input, $this->workspace()->path());
            $application = $container->get(Application::class);
            assert($application instanceof Application);
            $application->setAutoExit(false);
            $application->run($input, $container->get(ConsoleExtension::SERVICE_OUTPUT_STD));
        }

        $output = $this->workspace()->getContents('output');
        // hack to ignore the suite dates
        $output = preg_replace('{[0-9]{4}-[0-9]{2}-[0-9]{2}}', 'xxxx-xx-xx', $output);
        $output = preg_replace('{[0-9]{2}:[0-9]{2}:[0-9]{2}}', 'xx-xx-xx', $output);
        $output = preg_replace('{#[a-z0-9]{40}}', 'E3X6AeMdP7L9E7E0X0A7McP1L8E1EdXbAbMbP7La', $output);
        $output = str_replace(getcwd(), '/path/to', $output);
        $approval->approve($output);
    }

    /**
     * @return Generator<mixed>
     */
    public static function provideCommand(): Generator
    {
        /** @phpstan-ignore-next-line */
        foreach (glob(__DIR__ . '/../../examples/Command/*') as $file) {
            yield [
                $file,
            ];
        }
    }

    private function createExample(): void
    {
        $this->workspace()->put(
            'NothingBench.php',
            <<<'EOT'
<?php

class NothingBench { public function benchNothing(): void {}}
EOT
        );
        $this->workspace()->put(
            'MultipleSubjects.php',
            <<<'EOT'
<?php

class MultipleSubjectBench { 
    public function benchSubject1(): void {}
    public function benchSubject2(): void {}
    public function benchSubject3(): void {}
}
EOT
        );
    }
}
