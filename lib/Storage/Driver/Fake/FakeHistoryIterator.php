<?php

namespace PhpBench\Storage\Driver\Fake;

use PhpBench\Storage\HistoryEntry;
use PhpBench\Storage\HistoryIteratorInterface;

class FakeHistoryIterator implements HistoryIteratorInterface
{
    /**
     * @var array
     */
    private $entries;

    public function __construct(HistoryEntry ...$entries)
    {
        $this->entries = $entries;
    }

    /**
     * {@inheritDoc}
     */
    public function current(): HistoryEntry
    {
        return current($this->entries);
    }

    /**
     * {@inheritDoc}
     */
    public function next(): void
    {
        next($this->entries);
    }

    /**
     * {@inheritDoc}
     */
    public function key(): int
    {
        return key($this->entries);
    }

    /**
     * {@inheritDoc}
     */
    public function valid(): bool
    {
        return (bool)current($this->entries);
    }

    /**
     * {@inheritDoc}
     */
    public function rewind(): void
    {
        reset($this->entries);
    }
}
