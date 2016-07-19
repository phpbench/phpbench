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

namespace PhpBench\Environment;

/**
 * VCS specific information. All VCS providers should
 * return this object to ensure they all provide the same
 * information.
 */
class VcsInformation extends Information
{
    public function __construct($system, $branch, $version)
    {
        parent::__construct('vcs', [
            'system' => $system,
            'branch' => $branch,
            'version' => $version,
        ]);
    }
}
