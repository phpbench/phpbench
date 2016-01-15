<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Environment\Provider;

use PhpBench\Environment\ProviderInterface;
use PhpBench\Environment\VcsInformation;
use Symfony\Component\Process\Process;

/**
 * Return information about the git environment.
 *
 * NOTE: This class returns a VcsInformation class to ensure that
 *       all VCS providers provide the same keys. VCS providers
 *       should be mutually exlusive and "polymorphic".
 */
class Git implements ProviderInterface
{
    const SYSTEM = 'git';

    /**
     * {@inheritdoc}
     */
    public function isApplicable()
    {
        $index = sprintf('%s/.git', getcwd());
        if (file_exists($index)) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getInformation()
    {
        $cmd = 'git symbolic-ref HEAD';
        $process = $this->exec($cmd);

        if (0 !== $process->getExitCode() && stristr($process->getErrorOutput(), 'ref HEAD is not')) {
            $branchName = '(unnamed branch)';
        } elseif (0 === $process->getExitCode()) {
            preg_match('{^refs/heads/(.*)$}', $process->getOutput(), $matches);
            $branchName = $matches[1];
        } else {
            throw new \RuntimeException(sprintf(
                'Encountered error when determining git branch exide code: %s, stderr: "%s"',
                $process->getExitCode(),
                $process->getErrorOutput()
            ));
        }

        $commitshRef = sprintf(
            '%s/%s/%s',
            getcwd(),
            '.git/refs/heads',
            $branchName
        );

        if (!file_exists($commitshRef)) {
            $version = null;
        } else {
            $version = trim(file_get_contents($commitshRef));
        }

        return new VcsInformation(self::SYSTEM, $branchName, $version);
    }

    private function exec($cmd)
    {
        $process = new Process($cmd);
        $process->run();

        return $process;
    }
}
