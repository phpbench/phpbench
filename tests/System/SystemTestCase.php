<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Tests\System;

use Symfony\Component\Process\Process;

class SystemTestCase extends \PHPUnit_Framework_TestCase
{
    const TEST_FNAME = 'test.xml';

    public function tearDown()
    {
        if (file_exists(self::TEST_FNAME)) {
            unlink(self::TEST_FNAME);
        }
    }

    protected function getWorkingDir($workingDir = '.')
    {
        $dir = __DIR__ . '/' . $workingDir;

        return $dir;
    }

    public function phpbench($command, $workingDir = '.')
    {
        chdir($this->getWorkingDir($workingDir));
        $bin = __DIR__ . '/../../bin/phpbench --verbose';
        $process = new Process($bin . ' ' . $command);
        $process->run();

        return $process;
    }

    protected function assertExitCode($expected, Process $process)
    {
        $exitCode = $process->getExitCode();

        if ($exitCode !== $expected) {
            $this->fail(sprintf(
                'Expected exit code "%s" but got "%s": STDOUT: %s ERR: %s',
                $expected,
                $exitCode,
                $process->getOutput(),
                $process->getErrorOutput()
            ));
        }

        $this->assertEquals($expected, $exitCode);
    }

    protected function assertXPathCount($count, $xmlString, $query)
    {
        $dom = new \DOMDocument();
        $result = @$dom->loadXml($xmlString);

        if (false === $result) {
            throw new \RuntimeException(sprintf(
                'Could not load XML "%s"', $xmlString
            ));
        }

        $xpath = new \DOMXPath($dom);
        $dom->formatOutput = true;
        $nodeList = $xpath->query($query);
        $this->assertEquals($count, $nodeList->length);
    }
}
