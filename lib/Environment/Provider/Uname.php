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
 * Return the OS information (windows and unix).
 */
class Uname implements ProviderInterface
{
    public function isApplicable()
    {
        return true;
    }

    public function getInformation()
    {
        $uname = [];

        foreach ([
            'os' => 's',
            'host' => 'n',
            'release' => 'r',
            'version' => 'v',
            'machine' => 'm',
        ] as $key => $mode) {
            $uname[$key] = php_uname($mode);
        }

        return new Information('uname', $uname);
    }
}
