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
use PhpBench\Tests\IntegrationTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class SystemTestCase extends IntegrationTestCase
{
    /**
     * @var string
     */
    protected $fname;

    protected function setUp(): void
    {
        $this->fname = $this->workspace()->path('testfile');

        $this->workspace()->reset();

        $filesystem = new Filesystem();
        $filesystem->mirror(__DIR__ . '/env', $this->workspace()->path('/env'));
        $filesystem->mirror(__DIR__ . '/benchmarks', $this->workspace()->path('/benchmarks'));
        $filesystem->mirror(__DIR__ . '/bootstrap', $this->workspace()->path('/bootstrap'));
    }

    public function getBenchResult($benchmark = null, $extraCmd = '')
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

    public function phpbench(string $command, ?string $cwd = null): Process
    {
        $cwd = $this->workspace()->path($cwd);

        $bin = __DIR__ . '/../../bin/phpbench --verbose';
        $process = Process::fromShellCommandline($bin . ' ' . $command, $cwd);
        $process->run();

        return $process;
    }

    protected function assertExitCode($expected, Process $process): void
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

    protected function assertXPathCount($count, $xmlString, $query): void
    {
        $this->assertXPathExpression($count, $xmlString, sprintf('count(%s)', $query));
    }

    protected function assertXPathExpression($expected, $xmlString, $expression): void
    {
        $dom = new \DOMDocument();
        $result = @$dom->loadXML($xmlString);

        if (false === $result) {
            throw new \RuntimeException(sprintf(
                'Could not load XML "%s"',
                $xmlString
            ));
        }

        $xpath = new \DOMXPath($dom);
        $dom->formatOutput = true;
        $result = $xpath->evaluate($expression);
        $this->assertEquals($expected, $result);
    }
}
