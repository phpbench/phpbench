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

namespace PhpBench\Storage\UuidResolver;

use PhpBench\Registry\Registry;
use PhpBench\Storage\UuidResolverInterface;
use RuntimeException;

class LatestResolver implements UuidResolverInterface
{
    const LATEST_KEYWORD = 'latest';

    private $driverRegistry;

    public function __construct(Registry $driver)
    {
        $this->driverRegistry = $driver;
    }

    public function supports(string $reference): bool
    {
        return 0 === strpos($reference, self::LATEST_KEYWORD);
    }

    public function resolve(string $uuid): string
    {
        if (strtolower($uuid) === self::LATEST_KEYWORD) {
            return $this->getLatestUuid();
        }

        if (preg_match('{' . self::LATEST_KEYWORD . '-([0-9]+)}', $uuid, $matches)) {
            return $this->getNthUuid($matches[1]);
        }

        throw new RuntimeException(sprintf(
            'Could not resolve UUID "%s"', $uuid
        ));
    }

    private function getLatestUuid()
    {
        $history = $this->driverRegistry->getService()->history();

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
        $history = $this->driverRegistry->getService()->history();
        $entry = $history->current();

        for ($i = 0; $i <= $nth; $i++) {
            $entry = $history->current();
            $history->next();
        }

        return $entry->getRunId();
    }
}
