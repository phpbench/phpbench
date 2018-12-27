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

namespace PhpBench\Storage\Driver\Xml;

use PhpBench\Dom\Document;
use PhpBench\Serializer\XmlDecoder;
use PhpBench\Storage\HistoryEntry;
use PhpBench\Storage\HistoryIteratorInterface;

/**
 * XML file history iterator.
 *
 * This command will iterate over the suite collections created by the XML
 * storage driver.
 */
class HistoryIterator implements HistoryIteratorInterface
{
    private $xmlDecoder;
    private $path;

    private $years;
    private $months;
    private $days;
    private $entries;
    private $initialized;

    public function __construct(
        XmlDecoder $xmlDecoder,
        $path
    ) {
        $this->xmlDecoder = $xmlDecoder;
        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $this->init();

        return $this->entries->current();
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->init();

        $this->entries->next();

        if (!$this->entries->valid()) {
            $this->days->next();

            if (!$this->days->valid()) {
                $this->months->next();

                if (!$this->months->valid()) {
                    $this->years->next();

                    if ($this->years->valid()) {
                        $this->months = $this->getDirectoryIterator($this->years->current());
                    }
                }

                if ($this->months->valid()) {
                    $this->days = $this->getDirectoryIterator($this->months->current());
                }
            }

            if ($this->days->valid()) {
                $this->entries = $this->getEntryIterator();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        $this->init();
        $key = sprintf(
            '%s-%s-%s-%s',
            $this->years->key(),
            $this->months->key(),
            $this->days->key(),
            $this->entries->key()
        );

        return $key;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->init();
        $this->years->rewind();
        $this->months->rewind();
        $this->days->rewind();
        $this->entries->rewind();
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        $this->init();

        return $this->entries->valid();
    }

    private function init()
    {
        if ($this->initialized) {
            return;
        }

        $this->initialized = true;

        if (file_exists($this->path)) {
            $this->years = $this->getDirectoryIterator($this->path);
        } else {
            $this->years = new \ArrayIterator();
        }

        // create directory iterators for each part of the date sharding
        // (2016/01/01/<hash>.xml). if there is not valid entries for the
        // preceding shard, just create an empty array iterator.
        if ($this->years->valid()) {
            $this->months = $this->getDirectoryIterator($this->years->current());
        } else {
            $this->months = new \ArrayIterator();
        }

        if ($this->months->valid()) {
            $this->days = $this->getDirectoryIterator($this->months->current());
        } else {
            $this->days = new \ArrayIterator();
        }

        if ($this->days->valid()) {
            $this->entries = $this->getEntryIterator();
        } else {
            $this->entries = new \ArrayIterator();
        }
    }

    /**
     * Return an iterator for the history entries.
     *
     * We hydrate all of the entries for the "current" day.
     *
     * @return \ArrayIterator
     */
    private function getEntryIterator()
    {
        $files = $this->days->current();
        $files = new \DirectoryIterator($this->days->current());
        $historyEntries = [];

        foreach ($files as $file) {
            if (!$file->isFile()) {
                continue;
            }

            if ($file->getExtension() !== 'xml') {
                continue;
            }

            $historyEntries[] = $this->getHistoryEntry($file->getPathname());
        }
        usort($historyEntries, function ($entry1, $entry2) {
            if ($entry1->getDate()->format('U') === $entry2->getDate()->format('U')) {
                return;
            }

            return $entry1->getDate()->format('U') < $entry2->getDate()->format('U');
        });

        return new \ArrayIterator($historyEntries);
    }

    /**
     * Hydrate and return the history entry for the given path.
     *
     * The summary *should* used pre-calculated values from the XML
     * therefore reducing the normal overhead, however this code
     * is still quite expensive as we are creating the entire object
     * graph for each suite run.
     *
     * @param string $path
     *
     * @return HistoryEntry
     */
    private function getHistoryEntry($path)
    {
        $dom = new Document();
        $dom->load($path);
        $collection = $this->xmlDecoder->decode($dom);
        $suites = $collection->getSuites();
        $suite = reset($suites);
        $envInformations = $suite->getEnvInformations();

        $vcsBranch = null;

        if (isset($envInformations['vcs']['branch'])) {
            $vcsBranch = $envInformations['vcs']['branch'];
        }

        $summary = $suite->getSummary();
        $entry = new HistoryEntry(
            $suite->getUuid(),
            $suite->getDate(),
            $suite->getTag(),
            $vcsBranch,
            $summary->getNbSubjects(),
            $summary->getNbIterations(),
            $summary->getNbRevolutions(),
            $summary->getMinTime(),
            $summary->getMaxTime(),
            $summary->getMeanTime(),
            $summary->getMeanRelStDev(),
            $summary->getTotalTime()
        );

        return $entry;
    }

    /**
     * Return the iterator for a specific path (years, months, days).
     *
     * We sort by date in descending order.
     *
     * @return \ArrayIterator
     */
    private function getDirectoryIterator($path)
    {
        $nodes = new \DirectoryIterator($path);
        $dirs = [];

        foreach ($nodes as $dir) {
            if (!$dir->isDir()) {
                continue;
            }

            if ($dir->isDot()) {
                continue;
            }

            $dirs[hexdec($dir->getFilename())] = $dir->getPathname();
        }

        krsort($dirs);

        return new \ArrayIterator($dirs);
    }
}
