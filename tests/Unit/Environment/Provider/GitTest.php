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

namespace PhpBench\Tests\Unit\Environment\Provider;

use PhpBench\Environment\Provider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class GitTest extends TestCase
{
    private $provider;
    private $filesystem;
    private $testRepoDir;

    protected function setUp(): void
    {
        $this->filesystem = new Filesystem();

        $this->testRepoDir = __DIR__ . '/testRepo';
        $this->clean();
        $this->filesystem->mkdir($this->testRepoDir);
        chdir($this->testRepoDir);
        file_put_contents(sprintf('%s/foobar', $this->testRepoDir), 'Foobar');
        $this->exec('git init');
        $this->exec('git add foobar');
        $this->exec('git config user.email "test@example.com"');
        $this->exec('git config user.name "My Name"');

        $this->provider = new Provider\Git();
    }

    protected function tearDown(): void
    {
        $this->clean();
    }

    /**
     * It should return TRUE if the CWD is a git repository.
     */
    public function testIsApplicable()
    {
        $result = $this->provider->isApplicable();
        $this->assertTrue($result);
    }

    /**
     * It should return FALSE if the CWD is not a git repository.
     */
    public function testIsNotApplicable()
    {
        chdir(sys_get_temp_dir());
        $result = $this->provider->isApplicable();
        $this->assertFalse($result);
    }

    /**
     * It should return the VCS information for the current git repository.
     */
    public function testGetVcsInformation()
    {
        $info = $this->provider->getInformation();
        $this->assertEquals('git', $info['system']);
        $this->assertEquals('master', $info['branch']);
        $this->assertNull($info['version']); // no commit has yet been made
    }

    /**
     * It should show the commitsh.
     */
    public function testGetVcsCommitsh()
    {
        $this->exec('git commit -m "test"');
        $info = $this->provider->getInformation();
        $this->assertNotNull($info['version']); // no commit has yet been made
        $this->assertEquals(40, strlen($info['version']));
    }

    /**
     * It should show the branch.
     */
    public function testGetVcsBranch()
    {
        $this->exec('git commit -m "test"');

        $this->exec('git branch foobar');
        $this->exec('git checkout foobar');
        $info = $this->provider->getInformation();
        $this->assertEquals('foobar', $info['branch']);
    }

    /**
     * It should not be applicable if GIT is not available.
     */
    public function testNotApplicableIfGitNotFound()
    {
        $exeFinder = $this->prophesize(ExecutableFinder::class);
        $exeFinder->find('git', null)->willReturn(null);

        $provider = new Provider\Git($exeFinder->reveal());

        $this->assertFalse($provider->isApplicable());
    }

    private function clean()
    {
        if (file_exists($this->testRepoDir)) {
            $this->filesystem->remove(__DIR__ . '/testRepo');
        }
    }

    private function exec($cmd)
    {
        $proc = Process::fromShellCommandline($cmd);
        $exitCode = $proc->run();

        if ($exitCode !== 0) {
            throw new \RuntimeException(sprintf(
                'Could not execute command: %s',
                $proc->getErrorOutput()
            ));
        }
    }
}
