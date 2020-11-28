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

use PhpBench\Model\Tag;
use PhpBench\Storage\Exception\InvalidTagException;
use PhpBench\Storage\Exception\TagNotFoundException;
use PhpBench\Storage\HistoryEntry;
use PhpBench\Storage\StorageRegistry;
use PhpBench\Storage\UuidResolverInterface;

class TagResolver implements UuidResolverInterface
{
    const PREFIX = 'tag:';

    /**
     * @var StorageRegistry
     */
    private $storageRegistry;

    public function __construct(StorageRegistry $storageRegistry)
    {
        $this->storageRegistry = $storageRegistry;
    }

    public function supports(string $reference): bool
    {
        if (0 === strpos($reference, self::PREFIX)) {
            if (strlen($reference) === 4) {
                return false;
            }

            return true;
        }

        return false;
    }

    public function resolve(string $reference): string
    {
        $history = $this->storageRegistry->getService()->history();

        list($offset, $tag) = $this->tagAndOffset($reference);

        $count = 0;
        /** @var HistoryEntry $entry */
        foreach ($history as $entry) {
            if ($tag->__toString() === strtolower($entry->getTag())) {
                if ($count++ < $offset) {
                    continue;
                }

                return $entry->getRunId();
            }
        }

        if ($offset > 0) {
            throw new TagNotFoundException(sprintf(
                'Could not find entry %s entries before tag "%s"', $offset, $tag
            ));
        }

        throw new TagNotFoundException(sprintf(
            'Could not find entry for tag "%s"', $tag
        ));
    }

    /**
     * @return array{int, Tag}
     */
    private function tagAndOffset(string $reference): array
    {
        if (!preg_match(sprintf('{^tag:(%s)?-?([0-9]+)?$}', Tag::REGEX_PATTERN), $reference, $matches)) {
            throw new InvalidTagException(sprintf(
                'Could not parse tag "%s"', $reference
            ));
        }

        $tag = $matches[1] ? new Tag($matches[1]) : null;
        $offset = $matches[2] ?? 0;

        return [(int)$offset, $tag];
    }
}
