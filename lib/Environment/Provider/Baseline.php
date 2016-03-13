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

use PhpBench\Environment\Information;
use PhpBench\Environment\ProviderInterface;

/**
 * Run some simple algorithms to determine the relative speed of the machine.
 */
class Baseline implements ProviderInterface
{
    public function isApplicable()
    {
        return true;
    }

    public function getInformation()
    {
        return new Information(
            'baseline',
            array(
                'md5' => $this->measure(function () { md5('hello_world'); }),
                'nothing' => $this->measure(function () { }),
            )
        );
    }

    private function measure(\Closure $callback)
    {
        $start = microtime(true);
        $revs = 5E4;
        for ($i = 0; $i <= $revs; $i++) {
            $callback();
        }

        return (microtime(true) - $start) / $revs * 1E6;
    }
}
