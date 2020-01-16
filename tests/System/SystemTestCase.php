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

use PhpBench\Dom\Document;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class SystemTestCase extends TestCase
{
    protected $fname;
    protected $filesystem;
    protected $workspaceDir;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->workspaceDir = sys_get_temp_dir() . '/phpbench-tests';
        $this->filesystem->remove($this->workspaceDir);
        $this->filesystem->mkdir($this->workspaceDir);
        $this->fname = sprintf('%s/%s', $this->workspaceDir, 'testfile');
        $this->filesystem->mirror(__DIR__ . '/env', $this->workspaceDir . '/env');
        $this->filesystem->mirror(__DIR__ . '/benchmarks', $this->workspaceDir . '/benchmarks');
        $this->filesystem->mirror(__DIR__ . '/bootstrap', $this->workspaceDir . '/bootstrap');
    }

    public function getResult($benchmark = null, $extraCmd = '')
    {
        $benchmark = $benchmark ?: 'benchmarks/set4';

        $process = $this->phpbench(
            'run ' . $benchmark . ' --executor=debug --dump-file=' . $this->fname . ' ' . $extraCmd
        );

        if ($process->getExitCode() !== 0) {
            throw new \Exception('Could not generate test data:' . $process->getErrorOutput() . $process->getOutput());
        }

        $document = new Document();
        $document->load($this->fname);

        return $document;
    }

    /**
     * TODO: We should be using a generic temporary environment here
     *       as provided by PhpBench\Tests\Util\Workspace however this
     *       requires futher refactoring of the tests.
     */
    public function clearStorage()
    {
        foreach (['_storage', '_archive'] as $dirname) {
            $storageDir = __DIR__ . '/' . $dirname;

            if ($filesystem->exists($storageDir)) {
                $filesystem->remove($storageDir);
            }
        }
    }

    public function phpbench($command, $cwd = null)
    {
        $cwd = $this->workspaceDir . '/' . $cwd;

        chdir($cwd);
        $bin = __DIR__ . '/../../bin/phpbench --verbose';
        $process = Process::fromShellCommandline($bin . ' ' . $command);
        $process->run();

        return $process;
    }

    protected function assertExitCode($expected, Process $process)
    {
        $exitCode = $process->getExitCode();

        if ($exitCode !== $expected) {
            $this->fail(sprintf(
                'Expected exit code "%s" from "%s" but got "%s": STDOUT: %s ERR: %s',
                $expected,
                $process->getCommandLine(),
                $exitCode,
                $process->getOutput(),
                $process->getErrorOutput()
            ));
        }

        $this->assertEquals($expected, $exitCode);
    }

    protected function assertXPathCount($count, $xmlString, $query)
    {
        $this->assertXPathExpression($count, $xmlString, sprintf('count(%s)', $query));
    }

    protected function assertXPathExpression($expected, $xmlString, $expression)
    {
        $dom = new \DOMDocument();
        $result = @$dom->loadXML($xmlString);

        if (false === $result) {
            throw new \RuntimeException(sprintf(
                'Could not load XML "%s"', $xmlString
            ));
        }

        $xpath = new \DOMXPath($dom);
        $dom->formatOutput = true;
        $result = $xpath->evaluate($expression);
        $this->assertEquals($expected, $result);
    }

    protected function getWorkingDir()
    {
        return $this->workspaceDir;
    }
}
