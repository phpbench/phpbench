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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class SystemTestCase extends \PHPUnit_Framework_TestCase
{
    protected $fname;

    public function setUp()
    {
        $this->fname = tempnam(sys_get_temp_dir(), 'phpbench_report_output_test');
        $this->clearStorage();
    }

    public function tearDown()
    {
        unlink($this->fname);
        $this->clearStorage();
    }

    public function createResult($benchmark = null, $extraCmd = '')
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
        $filesystem = new Filesystem();
        $filesystem = new Filesystem();
        foreach (['_storage', '_archive'] as $dirname) {
            $storageDir = __DIR__ . '/' . $dirname;
            if ($filesystem->exists($storageDir)) {
                $filesystem->remove($storageDir);
            }
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
        $this->assertXPathExpression($count, $xmlString, sprintf('count(%s)', $query));
    }

    protected function assertXPathExpression($expected, $xmlString, $expression)
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
        $result = $xpath->evaluate($expression);
        $this->assertEquals($expected, $result);
    }
}
