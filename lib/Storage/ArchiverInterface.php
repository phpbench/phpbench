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

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Archivers handle archiving and retoring the contents of the configured
 * storage driver.
 */
interface ArchiverInterface
{
    /**
     * Archive all suites in the configured storage driver.
     *
     * In the case that a given record already exists in the archive,
     * then that record should be skipped.
     *
     * Progress should be written to the given console output class.
     *
     * @param OutputInterface $output
     */
    public function archive(OutputInterface $output);

    /**
     * Restore the archive to storage. If a given record exists in
     * the storage, it should be skipped.
     *
     * Progress should be written to the given console output class.
     */
    public function restore(OutputInterface $output);
}
