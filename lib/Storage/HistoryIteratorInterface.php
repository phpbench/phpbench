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

/**
 * Iterator interface which must be implemented by storage drivers.
 *
 * Each element should be an instance of HistoryEntry.
 */
interface HistoryIteratorInterface extends \Iterator
{
}
