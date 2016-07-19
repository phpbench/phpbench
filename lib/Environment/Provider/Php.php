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

namespace PhpBench\Environment\Provider;

use PhpBench\Benchmark\Remote\Launcher;
use PhpBench\Environment\Information;
use PhpBench\Environment\ProviderInterface;

/**
 * Return PHP information.
 */
class Php implements ProviderInterface
{
    private $launcher;
    private $remoteVersion;

    public function __construct(Launcher $launcher, $remoteVersion = false)
    {
        $this->launcher = $launcher;
        $this->remoteVersion = $remoteVersion;
    }

    public function isApplicable()
    {
        return true;
    }

    public function getInformation()
    {
        return new Information(
            'php',
            $this->getData()
        );
    }

    private function getData()
    {
        if (false === $this->remoteVersion) {
            return [
                'version' => phpversion(),
                'xdebug' => in_array('xdebug', get_loaded_extensions()),
            ];
        }

        return $this->launcher->payload(
            __DIR__ . '/template/php.template',
            []
        )->launch();
    }
}
