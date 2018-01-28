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

namespace PhpBench\Storage;

interface UuidResolverInterface
{
    public function supports(string $reference): bool;

    public function resolve(string $reference): string;
}
