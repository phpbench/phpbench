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

use PhpBench\Environment\Information;
use PhpBench\Environment\ProviderInterface;

/**
 * Return the load average for unix systems (1, 5, and 15 minute intervals).
 */
class UnixSysload implements ProviderInterface
{
    public function isApplicable()
    {
        return false === stristr(PHP_OS, 'win');
    }

    public function getInformation()
    {
        $load = sys_getloadavg();
        $load = array_combine([
            'l1', 'l5', 'l15',
        ], $load);

        return new Information('unix-sysload', $load);
    }
}
