<?php

namespace PhpBench\Tests\System;

class ConfigReferenceTest extends SystemTestCase
{
    public function testGenerateConfigReference(): void
    {
        $process = $this->phpbench('doc:config-reference --extension="PhpBench\\Extension\\DevelopmentExtension"');
        $process->mustRun();
        self::assertEquals(0, $process->getExitCode());
    }
}
