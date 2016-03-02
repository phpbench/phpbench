<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Storage\Driver\Xml;

use PhpBench\Storage\HistoryEntry;
use PhpBench\Storage\HistoryIteratorInterface;

/**
 * Lazily load history entries from the database.
 */
class HistoryIterator implements HistoryIteratorInterface
{
    private $position = 0;
    private $files;

    /**
     * @param Repository $repository
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $this->init();

        $filename = $this->files[$this->position];
        $meta = XmlDriverUtil::parseFilename($filename);

        $entry = new HistoryEntry(
            $meta['id'],
            $meta['date'],
            $meta['vcs_branch'],
            $meta['context']
        );

        return $entry;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        $this->init();

        return isset($this->files[$this->position]);
    }

    private function init()
    {
        if (null !== $this->files) {
            return;
        }

        $files = array();
        foreach (new \DirectoryIterator($this->path) as $file) {
            if (false === $file->isFile()) {
                continue;
            }

            if ('xml' !== $file->getExtension()) {
                continue;
            }

            $files[] = $file->getFilename();
        }

        rsort($files);

        $this->files = $files;
    }
}
