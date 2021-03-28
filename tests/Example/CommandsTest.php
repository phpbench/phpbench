<?php

namespace PhpBench\Tests\Example;

use Generator;
use PhpBench\Tests\IntegrationTestCase;
use PhpBench\Tests\Util\Approval;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use function getenv;

class CommandsTest extends IntegrationTestCase
{
    public function setUp(): void
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
        $command = trim($approval->getSection(1));

        if (0 !== strpos($command, 'phpbench')) {
            throw new RuntimeException(sprintf(
                'Command test command must start with `phpbench`, got "%s"',
                $command
            ));
        }

        $this->workspace()->put('phpbench.json', $approval->getSection(0));

        $process = Process::fromShellCommandline(
            sprintf('../../bin/%s', $command),
            $this->workspace()->path()
        );
        $process->mustRun();
        $output = $process->getOutput();
        $output = preg_replace('{[0-9]{4}-[0-9]{2}-[0-9]{2}}', 'xxxx-xx-xx', $output);
        $output = preg_replace('{[0-9]{2}:[0-9]{2}:[0-9]{2}}', 'xx-xx-xx', $output);
        $approval->approve($output);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideCommand(): Generator
    {
        /** @phpstan-ignore-next-line */
        foreach (glob(__DIR__ . '/../../examples/Command/*') as $file) {
            yield [
                $file,
            ];
        }
    }

    private function createExample()
    {
                $this->workspace()->put('NothingBench.php', <<<'EOT'
        <?php
        
        class NothingBench { public function benchNothing(): void {}}
        EOT
                );
    }
}
