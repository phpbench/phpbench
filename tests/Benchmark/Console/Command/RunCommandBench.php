<?php

namespace PhpBench\Tests\Benchmark\Console\Command;

use PhpBench\Examples\Benchmark\Macro\BaseBenchCase;
use Symfony\Component\Process\Process;

/**
 * @BeforeMethods("setUp")
 * @Revs(2)
 * @Iterations(2)
 */
class RunCommandBench extends BaseBenchCase
{
    public function setUp(): void
    {
        $this->createExample();
    }

    public function benchEmpyBenchmark(): void
    {
        Process::fromShellCommandline(
            sprintf('../../bin/phpbench run .'),
            $this->workspace()->path()
        )->mustRun();
    }

    private function createExample(): void
    {
        $this->workspace()->put('NothingBench.php', <<<'EOT'
<?php

class NothingBench { public function benchNothing(): void {}}
EOT
        );
    }
}
