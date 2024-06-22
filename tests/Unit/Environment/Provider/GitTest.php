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

use PhpBench\Environment\Provider\Git;
use PhpBench\Tests\IntegrationTestCase;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

use function sys_get_temp_dir;

class GitTest extends IntegrationTestCase
{
    private Git $provider;

    protected function setUp(): void
    {
        $this->workspace()->reset();
        $this->workspace()->put('foobar', 'Foobar');
        $this->exec('git init');
        $this->exec('git add foobar');
        $this->exec('git config user.email "test@example.com"');
        $this->exec('git config user.name "My Name"');

        $this->provider = new Git($this->workspace()->path());
    }

    /**
     * It should return TRUE if the CWD is a git repository.
     */
    public function testIsApplicable(): void
    {
        $result = $this->provider->isApplicable();
        $this->assertTrue($result);
    }

    /**
     * It should return FALSE if the CWD is not a git repository.
     */
    public function testIsNotApplicable(): void
    {
        $this->provider = new Git(sys_get_temp_dir());
        $result = $this->provider->isApplicable();
        $this->assertFalse($result);
    }

    /**
     * It should return the VCS information for the current git repository.
     */
    public function testGetVcsInformation(): void
    {
        $info = $this->provider->getInformation();
        $this->assertEquals('git', $info['system']);
        $this->assertTrue(in_array($info['branch'], ['main', 'master']));
        $this->assertNull($info['version']); // no commit has yet been made
    }

    /**
     * It should show the commitsh.
     */
    public function testGetVcsCommitsh(): void
    {
        $this->exec('git commit --no-gpg-sign -m "test"');
        $info = $this->provider->getInformation();
        $this->assertNotNull($info['version']); // no commit has yet been made
        $this->assertEquals(40, strlen((string) $info['version']));
    }

    /**
     * It should show the branch.
     */
    public function testGetVcsBranch(): void
    {
        $this->exec('git commit --no-gpg-sign -m "test"');

        $this->exec('git branch foobar');
        $this->exec('git checkout foobar');
        $info = $this->provider->getInformation();
        $this->assertEquals('foobar', $info['branch']);
    }

    /**
     * It should not be applicable if GIT is not available.
     */
    public function testNotApplicableIfGitNotFound(): void
    {
        $exeFinder = $this->prophesize(ExecutableFinder::class);
        $exeFinder->find('git', null)->willReturn(null);

        $provider = new Git(__DIR__, $exeFinder->reveal());

        $this->assertFalse($provider->isApplicable());
    }

    private function exec(string $cmd): void
    {
        $proc = Process::fromShellCommandline($cmd, $this->workspace()->path());
        $proc->mustRun();
    }
}
