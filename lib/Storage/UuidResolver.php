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

use PhpBench\Registry\Registry;

class UuidResolver
{
    private $driver;

    public function __construct(Registry $driver)
    {
        $this->driver = $driver;
    }

    public function resolve($uuid)
    {
        if (strtolower($uuid) === 'latest') {
            return $this->getLatestUuid();
        }

        if (preg_match('{latest-([0-9]+)}', $uuid, $matches)) {
            return $this->getNthUuid($matches[1]);
        }

        return $uuid;
    }

    private function getLatestUuid()
    {
        $history = $this->driver->getService()->history();

        $current = $history->current();

        if (!$current) {
            throw new \InvalidArgumentException(
                'No history present, therefore cannot retrieve latest UUID'
            );
        }

        return $current->getRunId();
    }

    private function getNthUuid($nth)
    {
        $history = $this->driver->getService()->history();
        $entry = $history->current();

        for ($i = 0; $i <= $nth; $i++) {
            $entry = $history->current();
            $history->next();
        }

        return $entry->getRunId();
    }
}
