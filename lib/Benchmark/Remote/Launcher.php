<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Benchmark\Remote;

/**
 * Build and execute tokenized scripts in separate processes.
 * The scripts should return a JSON encoded string.
 */
class Launcher
{
    /**
     * @var string
     */
    private $bootstrap;

    /**
     * @var string
     */
    private $basePath;

    /**
     * @param mixed string
     */
    public function __construct($bootstrap, $basePath)
    {
        $this->bootstrap = $bootstrap;
        $this->basePath = $basePath;
    }

    public function payload($template, array $tokens)
    {
        $bootstrap = $this->getBootstrapPath();

        $tokens['bootstrap'] = '';
        if (null !== $bootstrap) {
            if (!file_exists($bootstrap)) {
                throw new \InvalidArgumentException(sprintf(
                    'Bootstrap file "%s" does not exist.',
                    $bootstrap
                ));
            }
            $tokens['bootstrap'] = $bootstrap;
        }

        return new Payload($template, $tokens);
    }

    private function getBootstrapPath()
    {
        if (!$this->bootstrap) {
            return;
        }

        // if the path is absolute, return it unmodified
        if ('/' === substr($this->bootstrap, 0, 1)) {
            return $this->bootstrap;
        }

        return $this->basePath . '/' . $this->bootstrap;
    }
}
