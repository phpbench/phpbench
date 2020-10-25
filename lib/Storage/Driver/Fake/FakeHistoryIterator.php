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
    public function current()
    {
        return current($this->entries);
    }

    /**
     * {@inheritDoc}
     */
    public function next()
    {
        next($this->entries);
    }

    /**
     * {@inheritDoc}
     */
    public function key()
    {
        return key($this->entries);
    }

    /**
     * {@inheritDoc}
     */
    public function valid()
    {
        return current($this->entries);
    }

    /**
     * {@inheritDoc}
     */
    public function rewind()
    {
        reset($this->entries);
    }
}
