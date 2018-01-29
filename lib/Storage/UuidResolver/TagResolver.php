<?php

namespace PhpBench\Storage\UuidResolver;

use PhpBench\Storage\UuidResolverInterface;
use PhpBench\Registry\Registry;
use PhpBench\Storage\StorageRegistry;
use PhpBench\Storage\HistoryEntry;
use InvalidArgumentException;

class TagResolver implements UuidResolverInterface
{
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
        if (0 === strpos($reference, 'tag:')) {
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
        $tag = substr($reference, 4);

        /** @var HistoryEntry $entry */
        foreach ($history as $entry) {
            if (strtolower($tag) === strtolower($entry->getTag())) {
                return $entry->getRunId();
            }
        }

        throw new InvalidArgumentException(sprintf(
            'Could not find tag "%s"', $tag
        ));
    }
}
