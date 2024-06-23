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

class ConfigInitCommandTest extends SystemTestCase
{
    public function testInitialize(): void
    {
        $this->workspace()->reset();
        $process = $this->phpbench(
            'config:initialize',
        );
        self::assertEquals(0, $process->getExitCode());
        $output = $process->getErrorOutput();
        self::assertStringContainsString('Created', $output);
    }
}
