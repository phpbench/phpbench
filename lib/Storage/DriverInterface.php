<?php

/*
 * This file is part of the PHPBench package
 *
 * (c) Daniel Leech <daniel@dantleech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PhpBench\Storage;

use PhpBench\Expression\Constraint\Constraint;
use PhpBench\Model\SuiteCollection;

/**
 * Storage driver interface.
 */
interface DriverInterface
{
    /**
     * Store the given SuiteCollection.
     *
     * @param SuiteCollection $collection
     */
    public function store(SuiteCollection $collection);

    /**
     * Query the storage and return a SuiteCollection.
     *
     * @param Constraint $constraint
     *
     * @return SuiteCollection
     */
    public function query(Constraint $constraint);

    /**
     * Return the suite collection with the given run UUID.
     * If no suite is found an exception will be thrown.
     *
     * @param int $uuid
     *
     * @throws InvalidArgumentException
     *
     * @return SuiteCollection
     */
    public function fetch($uuid);

    /**
     * Return true if the driver has the given run UUID.
     */
    public function has($uuid);

    /**
     * Delete the run with the given run UUID.
     */
    public function delete($uuid);

    /**
     * Return a history iterator of HistoryEntries in descending
     * chronological order.
     *
     * @return HistoryIteratorInterface
     */
    public function history();
}
