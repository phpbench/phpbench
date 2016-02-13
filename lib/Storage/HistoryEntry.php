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

/**
 * Represents a summary of a run. Used when listing the history.
 */
class HistoryEntry
{
    private $runId;
    private $date;
    private $context;
    private $branch;

    public function __construct(
        $runId,
        \DateTime $date,
        $context,
        $branch
    ) {
        $this->runId = $runId;
        $this->date = $date;
        $this->context = $context;
        $this->branch = $branch;
    }

    public function getRunId()
    {
        return $this->runId;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function getVcsBranch()
    {
        return $this->branch;
    }
}
