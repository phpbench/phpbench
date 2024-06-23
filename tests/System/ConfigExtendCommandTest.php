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

namespace PhpBench\Tests\System;

use stdClass;

class ConfigExtendCommandTest extends SystemTestCase
{
    public function testExtend(): void
    {
        $this->workspace()->reset();
        $this->phpbench('config:initialize');
        $config = $this->getDecodedConfig();
        $process = $this->phpbench('config:extend generator expression test');
        self::assertExitCode(0, $process);

        $config = $this->getDecodedConfig();
        self::assertIsObject($config->{'report.generators'});
        self::assertIsObject($config->{'report.generators'}->{'test'});

        $process = $this->phpbench(
            [
                'run',
                __DIR__ . '/benchmarks/set4/NothingBench.php',
                '--report=test',
                '--iterations=1',
                '--revs=1'
            ]
        );

        self::assertExitCode(0, $process);
    }

    private function getDecodedConfig(): stdClass
    {
        return json_decode($this->workspace()->getContents('phpbench.json'));
    }
}
