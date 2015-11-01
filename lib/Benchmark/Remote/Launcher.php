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
     * @param mixed string
     */
    public function __construct($bootstrap)
    {
        $this->bootstrap = $bootstrap;
    }

    public function payload($template, array $tokens)
    {
        $tokens['bootstrap'] = '';
        if (null !== $this->bootstrap) {
            if (!file_exists($this->bootstrap)) {
                throw new \InvalidArgumentException(sprintf(
                    'Bootstrap file "%s" does not exist.',
                    $this->bootstrap
                ));
            }
            $tokens['bootstrap'] = $this->bootstrap;
        }

        return new Payload($template, $tokens);
    }
}
