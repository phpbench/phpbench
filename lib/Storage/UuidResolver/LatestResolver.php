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

use InvalidArgumentException;
use PhpBench\Registry\Registry;
use PhpBench\Storage\DriverInterface;
use PhpBench\Storage\HistoryEntry;
use PhpBench\Storage\UuidResolverInterface;
use RuntimeException;

class LatestResolver implements UuidResolverInterface
{
    final public const LATEST_KEYWORD = 'latest';

    /**
     * @param Registry<DriverInterface> $driverRegistry
     */
    public function __construct(private readonly Registry $driverRegistry)
    {
    }

    public function resolve(string $ref): ?string
    {
        if (!str_starts_with($ref, self::LATEST_KEYWORD)) {
            return null;
        }

        if (strtolower($ref) === self::LATEST_KEYWORD) {
            return $this->getLatestUuid();
        }

        if (preg_match('{' . self::LATEST_KEYWORD . '-([0-9]+)}', $ref, $matches)) {
            return $this->getNthUuid((int)$matches[1]);
        }

        throw new RuntimeException(sprintf(
            'Could not resolve ref "%s"',
            $ref
        ));
    }

    private function getLatestUuid(): ?string
    {
        $history = $this->driverRegistry->getService()->history();

        /** @var HistoryEntry|false $current */
        $current = $history->current();

        if ($current === false) {
            throw new InvalidArgumentException(
                'No history present, therefore cannot retrieve latest UUID'
            );
        }

        return $current->getRunId();
    }

    private function getNthUuid(int $nth): ?string
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
