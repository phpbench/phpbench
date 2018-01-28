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

namespace PhpBench\Extensions\Dbal\Storage\Driver\Dbal;

use PhpBench\Storage\HistoryEntry;
use PhpBench\Storage\HistoryIteratorInterface;

/**
 * Lazily load history entries from the database.
 */
class HistoryIterator implements HistoryIteratorInterface
{
    private $repository;
    private $statement;
    private $position = 0;
    private $actualPosition = null;
    private $current;

    /**
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $this->init();
        $current = $this->current;
        $entry = new HistoryEntry(
            $current['run_uuid'],
            new \DateTime($current['run_date']),
            $current['tag'],
            $current['vcs_branch'],
            $current['nb_subjects'],
            $current['nb_iterations'],
            $current['nb_revolutions'],
            $current['min_time'],
            $current['max_time'],
            $current['mean_time'],
            $current['mean_rstdev'],
            $current['total_time']
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

        return (bool) $this->current;
    }

    private function init()
    {
        if (null === $this->statement) {
            $this->statement = $this->repository->getHistoryStatement();
        }

        if ($this->position !== $this->actualPosition) {
            $this->current = $this->statement->fetch(\PDO::FETCH_ASSOC, \PDO::FETCH_ORI_ABS, $this->position);
            $this->actualPosition = $this->position;
        }
    }
}
