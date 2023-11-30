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

use DateTime;
use PhpBench\Model\Tag;

/**
 * Represents a summary of a run. Used when listing the history.
 */
class HistoryEntry
{
    public function __construct(private $runId, private readonly DateTime $date, private $tag, private $branch, private $nbSubjects, private $nbIterations, private $nbRevolutions, private $minTime, private $maxTime, private $meanTime, private $meanRelStDev, private $totalTime)
    {
    }

    public function getRunId()
    {
        return $this->runId;
    }

    public function getDate(): DateTime
    {
        return $this->date;
    }

    public function getTag(): ?Tag
    {
        return $this->tag ? new Tag($this->tag) : null;
    }

    public function getNbSubjects()
    {
        return $this->nbSubjects;
    }

    public function getNbIterations()
    {
        return $this->nbIterations;
    }

    public function getNbRevolutions()
    {
        return $this->nbRevolutions;
    }

    public function getVcsBranch()
    {
        return $this->branch;
    }

    public function getMinTime()
    {
        return $this->minTime;
    }

    public function getMaxTime()
    {
        return $this->maxTime;
    }

    public function getMeanTime()
    {
        return $this->meanTime;
    }

    public function getMeanRelStDev()
    {
        return $this->meanRelStDev;
    }

    public function getTotalTime()
    {
        return $this->totalTime;
    }
}
