<?php

namespace PhpBench\Storage\Driver\Fake;

use PhpBench\Storage\HistoryEntry;
use PhpBench\Storage\HistoryIteratorInterface;

class FakeHistoryIterator implements HistoryIteratorInterface
{
    /** @var \ArrayIterator<array-key, HistoryEntry> */
    private \ArrayIterator $entries;

    public function __construct(HistoryEntry ...$entries)
    {
        $this->entries = new \ArrayIterator($entries);
    }

    /**
     * {@inheritDoc}
     */
    public function current(): HistoryEntry
    {
        return $this->entries->current();
    }

    /**
     * {@inheritDoc}
     */
    public function next(): void
    {
        $this->entries->next();
    }

    /**
     * {@inheritDoc}
     */
    public function key(): int
    {
        return $this->entries->key();
    }

    /**
     * {@inheritDoc}
     */
    public function valid(): bool
    {
        return $this->entries->valid();
    }

    /**
     * {@inheritDoc}
     */
    public function rewind(): void
    {
        $this->entries->rewind();
    }
}
