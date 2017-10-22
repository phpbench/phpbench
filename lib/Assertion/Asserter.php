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

namespace PhpBench\Assertion;

use PhpBench\Registry\Config;
use PhpBench\Registry\RegistrableInterface;

interface Asserter extends RegistrableInterface
{
    public function assert(AssertionData $data, Config $config);
}
