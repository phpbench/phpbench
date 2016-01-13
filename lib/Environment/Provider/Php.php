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
 * Return PHP information.
 */
class Php implements ProviderInterface
{
    public function isApplicable()
    {
        return true;
    }

    public function getInformation()
    {
        return new Information(
            'php',
            array(
                'version' => PHP_VERSION,
            )
        );
    }
}
