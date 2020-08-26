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

namespace PhpBench\Storage;

use PhpBench\Model\SuiteCollection;

/**
 * Storage driver interface.
 */
interface DriverInterface
{
    /**
     * Store the given SuiteCollection.
     *
     * Optionally return a message which should be displayed
     * by the CLI interface after successful storage.
     *
     * @param SuiteCollection $collection
     *
     * @return string|null
     */
    public function store(SuiteCollection $collection);

    /**
     * Return the suite collection with the given run ID.
     * If no suite is found an exception will be thrown.
     *
     * @param int $runId
     *
     * @throws \InvalidArgumentException
     *
     * @return SuiteCollection
     */
    public function fetch($runId);

    /**
     * Return true if the driver has the given run ID.
     */
    public function has($runId);

    /**
     * Return a history iterator of HistoryEntries in descending
     * chronological order.
     *
     * @return HistoryIteratorInterface
     */
    public function history();
}
