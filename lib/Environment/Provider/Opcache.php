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

/**
 * Return PHP information.
 */
class Opcache extends AbstractRemoteProvider
{
    protected function name(): string
    {
        return 'opcache';
    }

    protected function template(): string
    {
        return __DIR__ . '/template/opcache.template';
    }
}
